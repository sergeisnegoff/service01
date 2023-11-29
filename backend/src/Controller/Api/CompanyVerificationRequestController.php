<?php


namespace App\Controller\Api;

use App\Model\CompanyVerificationRequest;
use App\Normalizer\CompanyVerificationRequestNormalizer;
use App\Service\Company\CompanyVerificationRequestList\CompanyVerificationRequestListContext;
use App\Service\Company\CompanyVerificationRequestService;
use App\Service\ListConfiguration\ListConfiguration;
use App\Validator\Constraints\NotBlank;
use Creonit\RestBundle\Annotation\Parameter\PathParameter;
use Creonit\RestBundle\Annotation\Parameter\QueryParameter;
use Creonit\RestBundle\Annotation\Parameter\RequestParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;

/**
 * @Route("/companyVerificationRequests")
 */
class CompanyVerificationRequestController extends AbstractController
{
    /**
     * Получить заявки на модерацию
     *
     * @QueryParameter("query", type="string", description="Поиск")
     * @QueryParameter("statusCode", type="string", description="Код статуса (см. модель CompanyVerificationRequest)")
     * @QueryParameter("page", type="integer", description="Страница")
     * @QueryParameter("limit", type="integer", description="Лимит")
     *
     * @Route("", methods={"GET"})
     */
    public function getCompanyVerificationRequests(
        RestHandler $handler,
        CompanyVerificationRequestService $verificationRequestService,
        ListConfiguration $configuration,
        NormalizerInterface $normalizer
    )
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $user = $this->getUser();

        $handler->checkPermission('ROLE_MODERATOR', $user);

        $handler->validate([
            'query' => [
                'statusCode' => [new Choice(['choices' => ['all'] + CompanyVerificationRequest::$statusCodes])],
            ],
        ]);

        $context = new CompanyVerificationRequestListContext();
        $context
            ->setQuery($request->query->get('query', ''))
            ->setStatusCode($request->query->get('statusCode', ''));

        $normalizeData = $normalizer->normalize(
            $verificationRequestService->getVerificationRequestList($context, $configuration),
            '',
            ['groups' => [CompanyVerificationRequestNormalizer::GROUP_WITH_COMPANY]]
        );
        $normalizeData['newCount'] = $verificationRequestService->countNewVerificationRequest();

        return $handler->response($normalizeData);
    }

    /**
     * Изменить статус заявки
     *
     * @PathParameter("id", type="integer", description="ID заявки")
     * @RequestParameter("statusCode", type="string", description="Код статуса (см. модель CompanyVerificationRequest)")
     *
     * @Route("/{id}/status", methods={"PUT"})
     */
    public function changeStatusCompanyVerificationRequests(
        RestHandler $handler,
        CompanyVerificationRequestService $verificationRequestService,
        $id
    )
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $user = $this->getUser();

        $handler->checkPermission('ROLE_MODERATOR', $user);
        $handler->checkFound($verificationRequest = $verificationRequestService->findPk($id));

        $validateData = [
            'statusCode' => [new NotBlank(), new Choice(['choices' => CompanyVerificationRequest::$statusCodes])]
        ];

        $statusCode = $request->request->get('statusCode', '');

        if ($statusCode == CompanyVerificationRequest::$statusCodes[$verificationRequest->getStatus()]) {
            $handler->error->send('Статус уже установлен');
        }

        if ($statusCode === CompanyVerificationRequest::$statusCodes[CompanyVerificationRequest::STATUS_FAILED]) {
            $validateData['answer'] = [new NotBlank(), new Length(['max' => 2000])];
        }

        $handler->validate([
            'request' => $validateData,
        ]);

        return $handler->response(
            $verificationRequestService->changeVerificationRequestStatus(
                $verificationRequest,
                $statusCode,
                $request->request->get('answer', '')
            )
        );
    }
}
