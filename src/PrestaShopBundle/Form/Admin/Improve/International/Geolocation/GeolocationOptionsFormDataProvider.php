<?php
/**
 * 2007-2020 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
declare(strict_types=1);
namespace PrestaShopBundle\Form\Admin\Improve\International\Geolocation;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;
use PrestaShop\PrestaShop\Core\Geolocation\GeoLite\GeoLiteCityCheckerInterface;
use PrestaShop\PrestaShop\Core\Validation\ValidatorInterface;

/**
 * Class GeolocationOptionsFormDataProvider is responsible for handling geolocation form data.
 */
final class GeolocationOptionsFormDataProvider implements FormDataProviderInterface
{
    /**
     * @var DataConfigurationInterface
     */
    private $dataConfiguration;

    /**
     * @var GeoLiteCityCheckerInterface
     */
    private $geoLiteCityChecker;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param DataConfigurationInterface $dataConfiguration
     * @param GeoLiteCityCheckerInterface $geoLiteCityChecker
     * @param ValidatorInterface $validator
     */
    public function __construct(
        DataConfigurationInterface $dataConfiguration,
        GeoLiteCityCheckerInterface $geoLiteCityChecker,
        ValidatorInterface $validator
    ) {
        $this->dataConfiguration = $dataConfiguration;
        $this->geoLiteCityChecker = $geoLiteCityChecker;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->dataConfiguration->getConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data)
    {
        $errors = [];

        if (empty($data['geolocation_countries'])) {
            $errors[] = [
                'key' => 'Country selection is invalid.',
                'parameters' => [],
                'domain' => 'Admin.International.Notification',
            ];
        }

        if (!empty($errors)) {
            return $errors;
        }

        return $this->dataConfiguration->updateConfiguration($data);
    }
}
