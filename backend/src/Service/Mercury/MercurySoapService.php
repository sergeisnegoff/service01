<?php
declare(strict_types=1);

namespace App\Service\Mercury;

use App\Model\MercuryRequest;
use App\Model\MercurySetting;
use App\Model\VeterinaryDocument;
use App\Service\Mercury\Exception\MercuryException;
use Exception;
use Psr\Log\LoggerInterface;
use SoapClient;
use SoapVar;

class MercurySoapService
{
    const DEFAULT_LIMIT = 1000;

    const
        MERCURY_WSDL = 'http://api.vetrf.ru/schema/platform/services/2.1-RC-last/ams-mercury-g2b.service_v2.1_production.wsdl',
        ENTERPRISE_WSDL = 'http://api.vetrf.ru/schema/platform/services/2.1-RC-last/EnterpriseService_v2.1_production.wsdl'
    ;

    const NAMESPACE_APPLICATIONS_V2 = 'http://api.vetrf.ru/schema/cdm/mercury/g2b/applications/v2';

    const
        METHOD_SUBMIT_APPLICATION_REQUEST = 'submitApplicationRequest',
        METHOD_RECEIVE_APPLICATION_RESULT = 'receiveApplicationResult',
        METHOD_GET_ACTIVITY_LOCATION_LIST = 'GetActivityLocationList',
        METHOD_GET_BUSINESS_ENTITY_BY_GUID = 'GetBusinessEntityByGUID',
        METHOD_GET_BUSINESS_ENTITY_LIST = 'GetBusinessEntityList'
    ;

    const PRODUCT_TYPE_MEAT_PRODUCTS = 1;
    const SERVICE_ID = 'mercury-g2b.service:2.1';

    private LoggerInterface $mercuryLogger;

    public function __construct(LoggerInterface $mercuryLogger)
    {
        $this->mercuryLogger = $mercuryLogger;
    }

    protected function getClient(MercurySetting $setting, string $wsdl = self::MERCURY_WSDL): SoapClient
    {
        return new SoapClient(
            $wsdl,
            [
                'login' => $setting->getLogin(),
                'password' => $setting->getPassword(),
                'soap_version' => SOAP_1_1,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'trace' => true,
                'features' => SOAP_USE_XSI_ARRAY_TYPE,
                'exceptions' => true,
            ]
        );
    }

    protected function createRequest(MercurySetting $setting, string $method): MercuryRequest
    {
        $request = new MercuryRequest();
        $request
            ->setCompany($setting->getCompany())
            ->setMethod($method)
            ->save();

        return $request;
    }

    public function getDocumentByUuid(MercurySetting $setting, string $uuid, string $enterpriseGuid): array
    {
        $request = $this->createRequest($setting, 'GetDocumentByUuid');

        $data = [
            'apiKey' => $setting->getApiKey(),
            'application' => [
                'serviceId' => self::SERVICE_ID,
                'issuerId' => $setting->getIssuerId(),
                'issueDate' => $request->getCreatedAt(DATE_ATOM),
                'data' => new SoapVar([
                    new SoapVar(
                        [
                            'localTransactionId' => $request->getId(),
                            'initiator' => [
                                'login' => $setting->getVeterinaryLogin(),
                            ],
                            'uuid' => $uuid,
                            'enterpriseGuid' => $enterpriseGuid,
                        ],
                        SOAP_ENC_OBJECT,
                        'GetVetDocumentByUuidRequest',
                        self::NAMESPACE_APPLICATIONS_V2,
                        'getVetDocumentByUuidRequest',
                        self::NAMESPACE_APPLICATIONS_V2
                    )
                ], SOAP_ENC_OBJECT)
            ]
        ];

        $submitResult = $this->getResults($setting, $request, $data);
        $receiveResult = $this->receiveApplicationResult($setting, $submitResult->application->applicationId);

        return json_decode(
            json_encode($receiveResult->application->result->any['getVetDocumentByUuidResponse']->vetDocument),
            true
        );
    }

    public function getBusinessEntityByGuid(MercurySetting $setting, string $guid): array
    {
        $request = $this->createRequest($setting, 'GetBusinessEntityByGUID');
        $result = $this->getResults(
            $setting,
            $request,
            ['guid' => $guid],
            self::METHOD_GET_BUSINESS_ENTITY_BY_GUID,
            self::ENTERPRISE_WSDL
        );

        return json_decode(json_encode($result), true);
    }

    public function getBusinessEntityList(MercurySetting $setting): array
    {
        $request = $this->createRequest($setting, 'GetBusinessEntityList');

        $data = [
            'listOptions' => [
                'count' => self::DEFAULT_LIMIT,
                'offset' => 0,
            ],
            'businessEntity' => [
                'guid' => $setting->getIssuerId(),
            ],
        ];

        $result = $this->getResults(
            $setting,
            $request,
            $data,
            self::METHOD_GET_BUSINESS_ENTITY_LIST,
            self::ENTERPRISE_WSDL
        );

        $result = json_decode(json_encode($result), true)['businessEntityList']['businessEntity'];

        if (isset($result['uuid'])) {
            return [$result];
        }

        return $result;
    }

    protected function getActivityLocationList(MercurySetting $setting): array
    {
        $request = $this->createRequest($setting, 'GetActivityLocationList');

        $data = [
            'listOptions' => [
                'count' => self::DEFAULT_LIMIT,
                'offset' => 0,
            ],
            'businessEntity' => [
                'guid' => $setting->getIssuerId(),
            ],
        ];

        $result = $this->getResults($setting, $request, $data, self::METHOD_GET_ACTIVITY_LOCATION_LIST, self::ENTERPRISE_WSDL);
        $list = $result->activityLocationList;

        if (is_array($list->location)) {
            return array_map(fn($location) => $location->enterprise->guid, $list->location);
        }

        return [$list->location->enterprise->guid];
    }

    public function getVeterinaryDocuments(MercurySetting $setting, int $offset = 0): array
    {
        $enterprisesGuid = $this->getActivityLocationList($setting);

        $out = [];
        $responseOffset = 0;

        foreach ($enterprisesGuid as $enterpriseGuid) {
            $request = $this->createRequest($setting, 'GetVetDocumentListRequest');

            $data = [
                'apiKey' => $setting->getApiKey(),
                'application' => [
                    'serviceId' => self::SERVICE_ID,
                    'issuerId' => $setting->getIssuerId(),
                    'issueDate' => $request->getCreatedAt(DATE_ATOM),
                    'data' => new SoapVar([
                        new SoapVar(
                            [
                                'localTransactionId' => $request->getId(),
                                'initiator' => [
                                    'login' => $setting->getVeterinaryLogin(),
                                ],
                                'listOptions' => [
                                    'count' => self::DEFAULT_LIMIT,
                                    'offset' => $offset,
                                ],
                                'enterpriseGuid' => $enterpriseGuid,
                            ],
                            SOAP_ENC_OBJECT,
                            'GetVetDocumentListRequest',
                            self::NAMESPACE_APPLICATIONS_V2,
                            'getVetDocumentListRequest',
                            self::NAMESPACE_APPLICATIONS_V2
                        )
                    ], SOAP_ENC_OBJECT)
                ]
            ];

            $submitResult = $this->getResults($setting, $request, $data);
            $receiveResult = $this->receiveApplicationResult($setting, $submitResult->application->applicationId);

            try {
                $vetDocumentList = $receiveResult->application->result->any['getVetDocumentListResponse']->vetDocumentList;
                $documents = json_decode(json_encode($vetDocumentList->vetDocument), true);
                $responseOffset = ceil($vetDocumentList->total / self::DEFAULT_LIMIT) - 1;

                if (isset($documents['uuid'])) {
                    $documents = [$documents];
                }

                $out[] = [
                    'enterpriseGuid' => $enterpriseGuid,
                    'documents' => $documents
                ];

            } catch (Exception $exception) {
            }
        }

        return [
            'offset' => $responseOffset,
            'items' => $out,
        ];
    }

    protected function getResults(
        MercurySetting $setting,
        MercuryRequest $request,
        array $data,
        string $method = self::METHOD_SUBMIT_APPLICATION_REQUEST,
        string $wsdl = self::MERCURY_WSDL
    ) {
        $client = $this->getClient($setting, $wsdl);

        try {
            $response = $client->$method($data);
            $request->success();

            $this->toLog($this->prepareLogMessage($method, $data, $response), 'info');

            return $response;

        } catch (Exception $exception) {
            $request->failed($exception->getMessage());
            $this->toLog($this->prepareLogMessage($method, $data, $exception));

            throw new MercuryException($exception->getMessage());
        }
    }

    protected function receiveApplicationResult(MercurySetting $setting, string $applicationId)
    {
        while (true) {
            $request = $this->createRequest($setting, 'ReceiveApplicationResult');
            $data = [
                'apiKey' => $setting->getApiKey(),
                'issuerId' => $setting->getIssuerId(),
                'applicationId' => $applicationId,
            ];

            $response = $this->getResults($setting, $request, $data, self::METHOD_RECEIVE_APPLICATION_RESULT);

            if ($response->application->status === 'REJECTED') {
                $error = $response->application->errors->error;
                $errorMessage = is_array($error) ? $error[0]->_ : $error->_;

                $request->failed($errorMessage);
                throw new MercuryException($errorMessage);
            }

            if ($response->application->status === 'COMPLETED') {
                return $response;
            }
        }
    }

    public function prepareExtinguishVeterinaryDocumentData(
        VeterinaryDocument $document,
        MercurySetting $setting,
        int $transactionId
    ): array {
        $documentData = json_decode($document->getData(), true);
        $referencedDocument = $documentData['referencedDocument'];

        $certifiedConsignment = $documentData['certifiedConsignment'];
        $transportInfo = $certifiedConsignment['transportInfo'];
        $batch = $certifiedConsignment['batch'];

        return [
            'apiKey' => $setting->getApiKey(),
            'application' => [
                'serviceId' => self::SERVICE_ID,
                'issuerId' => $setting->getIssuerId(),
                'issueDate' => date(DATE_ATOM),
                'data' => new SoapVar([
                    new SoapVar(
                        [
                            'localTransactionId' => $transactionId,
                            'initiator' => [
                                'login' => $setting->getVeterinaryLogin(),
                            ],
                            'enterpriseGuid' => $document->getEnterpriseGuid(),
                            'delivery' => [
                                'deliveryDate' => date(DATE_ATOM),
                                'consignor' => [
                                    'businessEntity' => [
                                        'guid' => $certifiedConsignment['consignor']['businessEntity']['guid'],
                                    ],
                                    'enterprise' => [
                                        'guid' => $certifiedConsignment['consignor']['enterprise']['guid'],
                                    ],
                                ],
                                'consignee' => [
                                    'businessEntity' => [
                                        'guid' => $certifiedConsignment['consignee']['businessEntity']['guid'],
                                    ],
                                    'enterprise' => [
                                        'guid' => $certifiedConsignment['consignee']['enterprise']['guid'],
                                    ],
                                ],
                                'consignment' => [
                                    'productType' => $batch['productType'],
                                    'product' => [
                                        'guid' => $batch['product']['guid'],
                                    ],
                                    'subProduct' => [
                                        'guid' => $batch['subProduct']['guid'],
                                    ],
                                    'productItem' => [
                                        'guid' => $batch['productItem']['guid'],
                                    ],
                                    'volume' => $batch['volume'],
                                    'unit' => [
                                        'guid' => $batch['unit']['guid'],
                                    ],
                                    'dateOfProduction' => [
                                        'firstDate' => [
                                            'year' => $batch['dateOfProduction']['firstDate']['year'],
                                            'month' => $batch['dateOfProduction']['firstDate']['month'],
                                            'day' => $batch['dateOfProduction']['firstDate']['day'],
                                        ],
                                    ],
                                    'expiryDate' => [
                                        'firstDate' => [
                                            'year' => $batch['expiryDate']['firstDate']['year'],
                                            'month' => $batch['expiryDate']['firstDate']['month'],
                                            'day' => $batch['expiryDate']['firstDate']['day'],
                                        ],
                                    ],
                                    'batchID' => $batch['batchID'],
                                    'perishable' => $batch['perishable'],
                                    'origin' => [
                                        'country' => [
                                            'guid' => $batch['origin']['country']['guid'],
                                        ],
                                        'producer' => [
                                            'enterprise' => [
                                                'guid' => $batch['origin']['producer']['enterprise']['guid'],
                                            ],
                                            'role' => $batch['origin']['producer']['role'],
                                        ],
                                    ],
                                    'lowGradeCargo' => $batch['lowGradeCargo'],
                                ],
                                'transportInfo' => [
                                    'transportType' => $transportInfo['transportType'],
                                    'transportNumber' => [
                                        'vehicleNumber' => $transportInfo['transportNumber']['vehicleNumber'],
                                    ],
                                ],
                                'transportStorageType' => $certifiedConsignment['transportStorageType'],
                                'accompanyingForms' => [
                                    'waybill' => [
                                        'issueSeries' => $referencedDocument['issueSeries'] ?? '',
                                        'issueNumber' => $referencedDocument['issueNumber'] ?? '',
                                        'issueDate' => $referencedDocument['issueDate'] ?? '',
                                        'type' => $referencedDocument['type'] ?? '',
                                    ],
                                    'vetCertificate' => [
                                        'uuid' => $document->getUuid(),
                                    ],
                                ],
                            ],
                            'deliveryFacts' => [
                                'vetCertificatePresence' => 'ELECTRONIC',
                                'docInspection' => [
                                    'responsible' => [
                                        'login' => $setting->getVeterinaryLogin(),
                                    ],
                                    'result' => 'CORRESPONDS',
                                ],
                                'vetInspection' => [
                                    'responsible' => [
                                        'login' => $setting->getVeterinaryLogin(),
                                    ],
                                    'result' => 'CORRESPONDS',
                                ],
                                'decision' => 'ACCEPT_ALL',
                            ],
                        ],
                        SOAP_ENC_OBJECT,
                        'ProcessIncomingConsignmentRequest',
                        self::NAMESPACE_APPLICATIONS_V2,
                        'processIncomingConsignmentRequest',
                        self::NAMESPACE_APPLICATIONS_V2
                    )
                ], SOAP_ENC_OBJECT)
            ]
        ];
    }

    public function extinguishVeterinaryDocument(VeterinaryDocument $document, MercurySetting $setting): void
    {
        $request = $this->createRequest($setting, 'ProcessIncomingConsignment');
        $data = $this->prepareExtinguishVeterinaryDocumentData($document, $setting, $request->getId());

        $submitResult = $this->getResults($setting, $request, $data);
        $this->receiveApplicationResult($setting, $submitResult->application->applicationId);
    }

    protected function prepareLogMessage(string $method, array $data, $response): string
    {
        return sprintf(
            'Method: [%s]. Data: [%s]. Response: [%s]',
            $method,
            print_r($data, true),
            $response instanceof Exception ? $response->getMessage() : print_r($response, true)
        );
    }

    protected function toLog($message, $function = 'error'): void
    {
        $this->mercuryLogger->$function($message);
    }
}
