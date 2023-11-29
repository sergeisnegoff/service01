<?php


namespace App\Service\Invoice\InvoiceData;


use App\Model\Company;
use App\Model\CompanyOrganizationShop;
use App\Model\CompanyOrganizationShopQuery;
use App\Model\CompanyQuery;
use App\Model\Counterparty;
use App\Model\CounterpartyQuery;
use App\Model\InvoiceProductQuery;
use App\Model\ProductQuery;
use App\Model\UnitQuery;
use App\Service\DataObject\DataObjectInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class InvoiceData implements DataObjectInterface
{
    protected ?CompanyOrganizationShop $shop = null;
    protected ?Company $buyer = null;
    protected ?string $code = '';
    protected ?string $createdAt = '';
    protected ?string $number = '';
    protected ?Counterparty $counterparty = null;

    /**
     * @return string|null
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * @param string|null $number
     */
    public function setNumber(?string $number): self
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * @param string|null $createdAt
     */
    public function setCreatedAt(?string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     */
    public function setCode(?string $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return Counterparty|null
     */
    public function getCounterparty(): ?Counterparty
    {
        return $this->counterparty;
    }

    /** @var InvoiceProductData[] */
    protected array $products = [];

    /**
     * @return CompanyOrganizationShop|null
     */
    public function getShop(): ?CompanyOrganizationShop
    {
        return $this->shop;
    }

    /**
     * @param CompanyOrganizationShop|null $shop
     */
    public function setShop(?CompanyOrganizationShop $shop): self
    {
        $this->shop = $shop;
        return $this;
    }

    /**
     * @param Company|null $buyer
     */
    public function setBuyer(?Company $buyer): self
    {
        $this->buyer = $buyer;
        return $this;
    }

    /**
     * @return Company|null
     */
    public function getBuyer(): ?Company
    {
        return $this->buyer;
    }

    /**
     * @return array
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @param array $products
     */
    public function setProducts(array $products): self
    {
        $this->products = $products;
        return $this;
    }

    public function initEntities(ParameterBag $bag): void
    {
        $shopId = $bag->get('shopId');
        $counterpartyId = $bag->get('counterpartyId');
        $buyerId = $bag->get('buyerId');
        $findFunction = 'findPk';

        if ($bag->getBoolean('findByCode')) {
            $findFunction = 'findOneByExternalCode';
        }

        $this->buyer = CompanyQuery::create()->$findFunction($buyerId);
        $this->shop = CompanyOrganizationShopQuery::create()->$findFunction($shopId);
        $this->counterparty = CounterpartyQuery::create()->$findFunction($counterpartyId);
    }

    public function initProducts(): void
    {
        $products = $this->getProducts();
        $this->products = [];

        if (!isset($products[0]['productId'])) {
            return;
        }

        foreach ($products as $product) {
            $invoiceProduct = new InvoiceProductData();
            $invoiceProduct
                ->setInvoiceProduct(InvoiceProductQuery::create()->findPk($product['id'] ?? 0))
                ->setProduct(ProductQuery::create()->findPk($product['productId'] ?? 0) ?? ProductQuery::create()->findOneByExternalCode($product['productId'] ?? null))
                ->setUnit(UnitQuery::create()->findPk((float)($product['unitId'] ?? 0)))
                ->setQuantity((float)($product['quantity'] ?? 0))
                ->setPrice((float)($product['price'] ?? 0))
                ->setPriceWithVat((float)($product['priceWithVat'] ?? 0))
                ->setTotalPrice((float)($product['totalPrice'] ?? 0))
                ->setTotalPriceVat((float)($product['totalPriceVat'] ?? 0))
                ->setTotalPriceWithVat((float)($product['totalPriceWithVat'] ?? 0))
                ->setVat((int)($product['vat'] ?? 0))
            ;

            $this->products[] = $invoiceProduct;
        }
    }

    public function appendProduct(InvoiceProductData $productData): self
    {
        $this->products[] = $productData;
        return $this;
    }

    public function validate(Company $company): array
    {
        $errors = new ParameterBag();

        if (!$this->getBuyer() && !$this->getShop()) {
            $errors->set('buyerId', 'Покупатель не найден');
        }

        if (!$this->getShop() && !$this->getBuyer()) {
            $errors->set('shopId', 'Торговая точка не найдена');
        }

        $productErrors = [];

        foreach ($this->getProducts() as $key => $product) {
            if (!$productObject = $product->getProduct()) {
                $productErrors[$key] = ['productId' => 'Выберите товар'];
                continue;
            }

            if (!$productObject->isEqualOwner($company->getUserRelatedByUserId())) {
                $productErrors[$key] = ['productId' => 'Выберите товар'];
            }
        }

        if ($productErrors) {
            $errors->set('products', $productErrors);
        }

        return $errors->all();
    }
}
