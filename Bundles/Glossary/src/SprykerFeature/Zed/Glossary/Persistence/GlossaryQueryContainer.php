<?php

/*
 * (c) Copyright Spryker Systems GmbH 2015
 */

namespace SprykerFeature\Zed\Glossary\Persistence;

use SprykerEngine\Zed\Kernel\Persistence\AbstractQueryContainer;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use SprykerEngine\Zed\Locale\Persistence\Propel\Map\SpyLocaleTableMap;
use SprykerFeature\Zed\Glossary\Persistence\Propel\SpyGlossaryKeyQuery;
use SprykerFeature\Zed\Glossary\Persistence\Propel\SpyGlossaryTranslationQuery;
use SprykerFeature\Zed\Glossary\Persistence\Propel\Map\SpyGlossaryKeyTableMap;
use SprykerFeature\Zed\Glossary\Persistence\Propel\Map\SpyGlossaryTranslationTableMap;

class GlossaryQueryContainer extends AbstractQueryContainer implements GlossaryQueryContainerInterface
{
    const TRANSLATION = 'translation';
    const TRANSLATION_IS_ACTIVE = 'translation_is_active';
    const KEY_IS_ACTIVE = 'key_is_active';
    const GLOSSARY_KEY = 'glossary_key';
    const GLOSSARY_KEY_IS_ACTIVE = 'glossary_key_is_active';
    const LOCALE = 'locale';

    /**
     * @param string $keyName
     *
     * @return SpyGlossaryKeyQuery
     */
    public function queryKey($keyName)
    {
        $query = $this->queryKeys();
        $query->filterByKey($keyName);

        return $query;
    }

    /**
     * @param string $keyName
     * @param string $localeName
     *
     * @return SpyGlossaryTranslationQuery
     */
    public function queryTranslationByNames($keyName, $localeName)
    {
        $query = $this->queryTranslations();
        $query
            ->useGlossaryKeyQuery()
            ->filterByKey($keyName)
            ->endUse()

            ->useLocaleQuery()
            ->filterByLocaleName($localeName)
            ->endUse()
        ;

        return $query;
    }

    /**
     * @param int $idKey
     * @param int $idLocale
     *
     * @return SpyGlossaryTranslationQuery
     */
    public function queryTranslationByIds($idKey, $idLocale)
    {
        $query = $this->queryTranslations();
        $query
            ->filterByFkGlossaryKey($idKey)
            ->filterByFkLocale($idLocale)
        ;

        return $query;
    }

    /**
     * @param int $idSpyGlossaryTranslation
     *
     * @return SpyGlossaryTranslationQuery
     */
    public function queryTranslationById($idSpyGlossaryTranslation)
    {
        $query = $this->queryTranslations();
        $query->filterByIdGlossaryTranslation($idSpyGlossaryTranslation);

        return $query;
    }

    /**
     * @return SpyGlossaryTranslationQuery
     */
    public function queryTranslations()
    {
        return SpyGlossaryTranslationQuery::create();
    }

    /**
     * @param string $localeName
     *
     * @return SpyGlossaryTranslationQuery
     */
    public function queryTranslationsByLocale($localeName)
    {
        $query = $this->queryTranslations();
        $query
            ->useLocaleQuery()
            ->filterByLocaleName($localeName)
            ->endUse()
        ;

        return $query;
    }

    /**
     * @param string $keyName
     *
     * @return SpyGlossaryTranslationQuery
     */
    public function queryTranslationsByKey($keyName)
    {
        $query = $this->queryTranslations();
        $query
            ->useGlossaryKeyQuery()
            ->filterByKey($keyName)
            ->endUse()
        ;

        return $query;
    }

    /**
     * @param SpyGlossaryTranslationQuery $query
     *
     * @return ModelCriteria
     */
    public function joinTranslationQueryWithKeysAndLocales(SpyGlossaryTranslationQuery $query)
    {
        $query
            ->joinLocale()
            ->withColumn(SpyLocaleTableMap::COL_LOCALE_NAME, self::LOCALE)
            ->joinGlossaryKey()
            ->withColumn(SpyGlossaryTranslationTableMap::COL_VALUE, self::TRANSLATION)
            ->withColumn(SpyGlossaryTranslationTableMap::COL_IS_ACTIVE, self::TRANSLATION_IS_ACTIVE)
            ->withColumn(SpyGlossaryKeyTableMap::COL_KEY, self::GLOSSARY_KEY)
            ->withColumn(SpyGlossaryKeyTableMap::COL_IS_ACTIVE, self::GLOSSARY_KEY_IS_ACTIVE)
        ;

        return $query;
    }

    /**
     * @param array $relevantLocales
     *
     * @return ModelCriteria
     */
    public function queryAllMissingTranslations(array $relevantLocales)
    {
        $keyQuery = $this->queryAllPossibleTranslations($relevantLocales);
        $keyQuery
            ->where(SpyGlossaryTranslationTableMap::COL_VALUE . '' . ModelCriteria::ISNULL)
        ;

        return $keyQuery;
    }

    /**
     * @param array $relevantLocales
     *
     * @return ModelCriteria
     * @throws PropelException
     */
    public function queryAllPossibleTranslations(array $relevantLocales)
    {
        $keyQuery = $this->queryKeys();

        return $this->joinKeyQueryWithRelevantLocalesAndTranslations($keyQuery, $relevantLocales);
    }

    /**
     * @return SpyGlossaryKeyQuery
     */
    public function queryKeys()
    {
        return SpyGlossaryKeyQuery::create();
    }

    /**
     * @param SpyGlossaryKeyQuery $keyQuery
     * @param array $relevantLocales
     *
     * @return ModelCriteria
     * @throws PropelException
     */
    protected function joinKeyQueryWithRelevantLocalesAndTranslations(
        SpyGlossaryKeyQuery $keyQuery,
        array $relevantLocales
    ) {
        $keyLocaleCrossJoin = new ModelJoin();
        $keyLocaleCrossJoin->setJoinType(Criteria::JOIN);

        /**
         * @param string $value
         *
         * @return string
         */
        $quoteFunction = function ($value) {
            return "'$value'";
        };

        $quotedLocales = array_map($quoteFunction, $relevantLocales);

        $keyLocaleCrossJoin
            ->setTableMap(new TableMap())
            ->setLeftTableName('spy_glossary_key')
            ->setRightTableName('spy_locale')
            ->addCondition('id_glossary_key', 'id_locale', ModelCriteria::NOT_EQUAL)
        ;

        $translationLeftJoin = new ModelJoin();
        $translationLeftJoin->setJoinType(Criteria::LEFT_JOIN);
        $translationLeftJoin
            ->setTableMap(new TableMap())
            ->setLeftTableName('spy_glossary_key')
            ->setRightTableName('spy_glossary_translation')
            ->addCondition('id_glossary_key', 'fk_glossary_key')
        ;

        return $keyQuery
            ->addJoinObject($keyLocaleCrossJoin, 'spy_locale')
            ->addJoinObject($translationLeftJoin, 'spy_glossary_translation')
            ->addJoinCondition('spy_glossary_translation', 'spy_locale.id_locale = spy_glossary_translation.fk_locale')
            ->addJoinCondition('spy_locale', 'spy_locale.locale_name  IN ('  . implode($quotedLocales, ', ') . ')')
        ;
    }

    /**
     * @param ModelCriteria $query
     *
     * @return ModelCriteria
     */
    public function queryDistinctKeysFromQuery(ModelCriteria $query)
    {
        $query
            ->distinct('key')
            ->withColumn(SpyGlossaryKeyTableMap::COL_ID_GLOSSARY_KEY, 'value')
            ->withColumn(SpyGlossaryKeyTableMap::COL_KEY, 'label')
        ;

        return $query;
    }

    /**
     * @param ModelCriteria $query
     *
     * @return ModelCriteria
     */
    public function queryDistinctLocalesFromQuery(ModelCriteria $query)
    {
        $query
            ->distinct('locale_name')
            ->withColumn(SpyLocaleTableMap::COL_ID_LOCALE, 'value')
            ->withColumn(SpyLocaleTableMap::COL_LOCALE_NAME, 'label')
        ;

        return $query;
    }

    /**
     * @param int $idKey
     * @param array $relevantLocales
     *
     * @return ModelCriteria
     */
    public function queryMissingTranslationsForKey($idKey, array $relevantLocales)
    {
        $keyQuery = $this->queryKeyById($idKey);
        $keyQuery = $this->joinKeyQueryWithRelevantLocalesAndTranslations($keyQuery, $relevantLocales);
        $keyQuery
            ->where(SpyGlossaryTranslationTableMap::COL_VALUE . '' . ModelCriteria::ISNULL)
        ;

        return $keyQuery;
    }

    /**
     * @param int $idKey
     *
     * @return SpyGlossaryKeyQuery
     */
    protected function queryKeyById($idKey)
    {
        $query = SpyGlossaryKeyQuery::create();
        $query->filterByIdGlossaryKey($idKey);

        return $query;
    }
}