<?php

/**
 * 2007-2019 PrestaShop SA and Contributors
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
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\PrestaShop\Core\Localization\Currency\DataLayer;

use Language;
use PrestaShop\PrestaShop\Core\Currency\CurrencyDataProviderInterface;
use PrestaShop\PrestaShop\Core\Data\Layer\AbstractDataLayer;
use PrestaShop\PrestaShop\Core\Data\Layer\DataLayerException;
use PrestaShop\PrestaShop\Core\Localization\CLDR\LocaleRepository as CldrLocaleRepository;
use PrestaShop\PrestaShop\Core\Localization\Currency\CurrencyData;
use PrestaShop\PrestaShop\Core\Localization\Currency\LocalizedCurrencyId;
use PrestaShop\PrestaShop\Core\Localization\Currency\CurrencyDataLayerInterface;
use PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException;

/**
 * Currency Database data layer.
 *
 * Provides and persists currency data from/into database
 */
class CurrencyDatabase extends AbstractDataLayer implements CurrencyDataLayerInterface
{
    /**
     * CLDR locale repository.
     *
     * Provides LocaleData objects
     *
     * @var CldrLocaleRepository
     */
    protected $cldrLocaleRepository;

    /**
     * @var CurrencyDataProviderInterface
     */
    protected $dataProvider;

    /**
     * @param CurrencyDataProviderInterface $dataProvider
     * @param CldrLocaleRepository $cldrLocaleRepository
     */
    public function __construct(
        CurrencyDataProviderInterface $dataProvider,
        CldrLocaleRepository $cldrLocaleRepository
    ) {
        $this->dataProvider = $dataProvider;
        $this->cldrLocaleRepository = $cldrLocaleRepository;
        $this->isWritable = false;
    }

    /**
     * Set the lower layer.
     * When reading data, if nothing is found then it will try to read in the lower data layer
     * When writing data, the data will also be written in the lower data layer.
     *
     * @param currencyDataLayerInterface $lowerLayer
     *                                               The lower data layer
     *
     * @return self
     */
    public function setLowerLayer(CurrencyDataLayerInterface $lowerLayer)
    {
        $this->lowerDataLayer = $lowerLayer;

        return $this;
    }

    /**
     * Actually read a data object into the current layer.
     *
     * Data is read into database
     *
     * @param LocalizedCurrencyId $currencyDataId
     *                                            The CurrencyData object identifier (currency code + locale code)
     *
     * @return CurrencyData|null
     *                           The wanted CurrencyData object (null if not found)
     *
     * @throws LocalizationException
     *                               When $currencyDataId is invalid
     */
    protected function doRead($currencyDataId)
    {
        if (!$currencyDataId instanceof LocalizedCurrencyId) {
            throw new LocalizationException('First parameter must be an instance of ' . LocalizedCurrencyId::class);
        }

        $localeCode = $currencyDataId->getLocaleCode();
        $currencyCode = $currencyDataId->getCurrencyCode();
        $currencyEntity = $this->dataProvider->getCurrencyByIsoCodeAndLocale($currencyCode, $localeCode);

        if (null === $currencyEntity) {
            return null;
        }

        $currencyData = new CurrencyData();
        $currencyData->setIsoCode($currencyEntity->iso_code);
        $currencyData->setNumericIsoCode($currencyEntity->numeric_iso_code);
        $currencyData->setPrecision($currencyEntity->precision);
        $currencyData->setNames([$localeCode => $currencyEntity->name]);
        $currencyData->setSymbols([$localeCode => $currencyEntity->symbol]);

        $idLang = Language::getIdByLocale($localeCode, true);
        $currencyPattern = $currencyEntity->getPattern($idLang);
        if (!empty($currencyPattern)) {
            $currencyData->setPatterns([$localeCode => $currencyEntity->getPattern($idLang)]);
        }

        return $currencyData;
    }

    /**
     * Actually write a data object into the current layer
     * Here, this is a DB insert/update...
     *
     * @param LocalizedCurrencyId $currencyDataId
     *                                            The CurrencyData object identifier (currency code + locale code)
     * @param CurrencyData $currencyData
     *                                   The data object to be written
     *
     * @throws DataLayerException
     *                            If something goes wrong when trying to write into DB
     * @throws LocalizationException
     *                               When $currencyDataId is invalid
     */
    protected function doWrite($currencyDataId, $currencyData)
    {
        // We should not save anything in this layer. The CLDR or its Repository nor any of its layers
        // should modify the database. This could override customization added by the user with default
        // CLDR values. Any changes on the database must be managed through the backoffice and the appropriate
        // commands/handlers
    }
}
