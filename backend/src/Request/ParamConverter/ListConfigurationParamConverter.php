<?php


namespace App\Request\ParamConverter;


use App\Service\ListConfiguration\ListConfiguration;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class ListConfigurationParamConverter implements ParamConverterInterface
{
    public function apply(Request $request, ParamConverter $configuration)
    {
        $listConfiguration = ListConfiguration::fromRequest($request);
        $request->attributes->set($configuration->getName(), $listConfiguration);
        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() === ListConfiguration::class;
    }
}
