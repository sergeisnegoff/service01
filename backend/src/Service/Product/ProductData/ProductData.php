<?php


namespace App\Service\Product\ProductData;


use App\Model\Company;
use App\Model\ProductBrand;
use App\Model\ProductBrandQuery;
use App\Model\ProductCategory;
use App\Model\ProductCategoryQuery;
use App\Model\ProductManufacturer;
use App\Model\ProductManufacturerQuery;
use App\Model\Unit;
use App\Model\UnitQuery;
use App\Service\DataObject\DataObjectInterface;
use App\Service\Product\Exception\ProductDataFillException;
use Symfony\Component\HttpFoundation\ParameterBag;

class ProductData implements DataObjectInterface
{
    protected Company $company;
    protected ?ProductBrand $brand = null;
    protected ProductCategory $category;
    protected ?ProductManufacturer $manufacturer = null;
    protected ?Unit $unit = null;

    protected float $price = 0;

    protected string $nomenclature = '';
    protected string $article = '';
    protected string $barcode = '';
    protected string $quant = '';
    protected string $vat = '';
    protected string $code = '';

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getVat(): string
    {
        return $this->vat;
    }

    /**
     * @param string $vat
     */
    public function setVat(string $vat): self
    {
        $this->vat = $vat;
        return $this;
    }

    /**
     * @return Unit|null
     */
    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function setUnit(?Unit $unit = null): self
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * @return ProductManufacturer|null
     */
    public function getManufacturer(): ?ProductManufacturer
    {
        return $this->manufacturer;
    }

    /**
     * @param ProductManufacturer|null $manufacturer
     */
    public function setManufacturer(?ProductManufacturer $manufacturer): self
    {
        $this->manufacturer = $manufacturer;
        return $this;
    }

    /**
     * @return Company
     */
    public function getCompany(): Company
    {
        return $this->company;
    }

    /**
     * @param Company $company
     */
    public function setCompany(Company $company): self
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return ProductBrand|null
     */
    public function getBrand(): ?ProductBrand
    {
        return $this->brand;
    }

    /**
     * @param ProductBrand|null $brand
     */
    public function setBrand(?ProductBrand $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @return ProductCategory
     */
    public function getCategory(): ProductCategory
    {
        return $this->category;
    }

    /**
     * @param ProductCategory $category
     */
    public function setCategory(ProductCategory $category): self
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return string
     */
    public function getNomenclature(): string
    {
        return $this->nomenclature;
    }

    /**
     * @param string $nomenclature
     */
    public function setNomenclature(string $nomenclature): self
    {
        $this->nomenclature = $nomenclature;
        return $this;
    }

    /**
     * @return string
     */
    public function getArticle(): string
    {
        return $this->article;
    }

    /**
     * @param string $article
     */
    public function setArticle(string $article): self
    {
        $this->article = $article;
        return $this;
    }

    /**
     * @return string
     */
    public function getBarcode(): string
    {
        return $this->barcode;
    }

    /**
     * @param string $barcode
     */
    public function setBarcode(string $barcode): self
    {
        $this->barcode = $barcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuant(): string
    {
        return $this->quant;
    }

    /**
     * @param string $quant
     */
    public function setQuant(string $quant): self
    {
        $this->quant = $quant;
        return $this;
    }

    public function fillEntities(ParameterBag $bag)
    {
        $findFunction = 'findOneByExternalCode';

        if (!$bag->getBoolean('findByCode')) {
            $findFunction = 'findPk';
        }

        $fields = [
            'brandId' => [
                'closure' => fn() => ProductBrandQuery::create()->$findFunction($bag->get('brandId')),
                'message' => 'Бренд не найден',
            ],
            'manufacturerId' => [
                'closure' => fn() => ProductManufacturerQuery::create()->$findFunction($bag->get('manufacturerId')),
                'message' => 'Производитель не найден',
            ],
            'categoryId' => [
                'closure' => fn() => ProductCategoryQuery::create()->$findFunction($bag->get('categoryId')),
                'message' => 'Категория не найдена',
            ],
            'unitId' => [
                'closure' => fn() => UnitQuery::create()->findPk($bag->get('unitId')),
                'message' => 'Единица измерения не найдена',
            ],
        ];

        foreach ($fields as $key => $data) {
            if (!$bag->get($key)) {
                continue;
            }

            $closure = $data['closure'];
            $entity = $closure();

            if (!$entity) {
                throw (new ProductDataFillException($data['message'] . sprintf(' Id: %s', $bag->get($key))))->setField($key);
            }

            $this->setEntity($entity);
        }
    }

    protected function setEntity($entity)
    {
        if ($entity instanceof ProductBrand) {
            $this->setBrand($entity);

        } else if ($entity instanceof ProductManufacturer) {
            $this->setManufacturer($entity);

        } else if ($entity instanceof ProductCategory) {
            $this->setCategory($entity);

        } else if ($entity instanceof Unit) {
            $this->setUnit($entity);
        }

        return $this;
    }
}
