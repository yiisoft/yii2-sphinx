Yii Framework 2 sphinx extension Change Log
===========================================

2.0.10 February 13, 2018
------------------------

- Bug #90: Fixed `yii\sphinx\Schema::findColumns()` unable to merge 'field' and 'attribute' columns with same name (maz0717, klimov-paul)
- Bug #92: Fixed `yii\sphinx\QueryBuilder::buildInCondition()` incompatibility with PHP 7.2 (klimov-paul)
- Enh #93: `yii\sphinx\QueryBuilder::callSnippets()` now automatically casts snippet source to string (klimov-paul)
- Enh: `yii\sphinx\QueryBuilder` now supports `Traversable` objects for use in `in` conditions (klimov-paul)


2.0.9 November 03, 2017
-----------------------

- Bug #75: Fixed empty MVA field value is fetches as array with null element instead of empty array (batyrmastyr, klimov-paul)
- Bug #78: Fixed `yii\sphinx\Query::where()` does not add params from directly passed `yii\db\Expression` (klimov-paul)
- Bug #82: Fixed `yii\sphinx\Query::select()` does not apply alias for `yii\db\Expression` value (klimov-paul)
- Bug #83: Fixed `yii\sphinx\ActiveRecord::update()` causes attribute value lost in case of field update (klimov-paul)
- Bug #87: Fixed `yii\sphinx\Command::getRawSql()` does not parse float params (klimov-paul)
- Bug: Usage of deprecated `yii\base\Object` changed to `yii\base\BaseObject` allowing compatibility with PHP 7.2 (klimov-paul)
- Enh #73: `isRuntime` field of `yii\sphinx\IndexSchema` renamed to `isRt` for consistency with official docs (klimov-paul)


2.0.8 May 15, 2017
------------------

- Bug #71: Fixed PHP type for `sql_attr_timestamp` attribute incorrectly detected as string (klimov-paul)


2.0.7 February 15, 2017
-----------------------

- Bug #69: Fixed `yii\sphinx\Query::andFilterWhere()` quotes integer column value in case comparison operator is used (klimov-paul)
- Enh #67: Added support for `yii\db\QueryInterface::emulateExecution()` to force returning an empty result for a query (klimov-paul)
- Enh #70: Added `filterMatch()` method to `yii\sphinx\MatchExpression` to allow easy addition of search filter conditions by ignoring empty search fields (klimov-paul)


2.0.6 September 02, 2016
------------------------

- Bug #8: Fixed usage of the float values in SphinxQL bound params (klimov-paul)
- Bug #45: Fixed `yii\sphinx\Schema` unable to determine primary key for distribute index (klimov-paul)
- Bug #61: Fixed `yii\sphinx\QueryBuilder::callSnippets()` unable to handle 'match' specified as `yii\db\Expression` instance (klimov-paul)
- Enh #26: Added `yii\sphinx\Query::groupLimit` allowing limit matches in 'group by' (klimov-paul)
- Enh #53: Added `yii\sphinx\MatchExpression` allowing advanced composition of 'MATCH' expressions (sa-kirich, klimov-paul)
- Enh #56: `yii\sphinx\Schema` now able to get schema for distributed index in case at least one (not only the first one) of local indexes is available (kdietrich, klimov-paul)
- Enh #58: Added fallback for distributed index columns detection at `yii\sphinx\Schema` (klimov-paul)


2.0.5 September 23, 2015
------------------------

- Bug #13: Fixed `yii\sphinx\ActiveDataProvider` breaks the pagination if `yii\data\Pagination::validatePage` enabled even, if `yii\sphinx\Query::showMeta` is not set (klimov-paul)
- Bug #21: `yii\sphinx\Query` unable to retrieve facet named in camel-case notation (klimov-paul)
- Bug #27: Fixed `yii\sphinx\ActiveQuery::search()` produces 'unbuffered query' error if 'facet' or 'show meta' are used (klimov-paul)
- Bug #30: Fixed `yii\sphinx\ActiveQuery` does not perform typecast for condition values (klimov-paul)
- Bug #31: Fixed `yii\sphinx\QueryBuilder::buildInCondition()` fails produces invalid SphinxQL for empty values (klimov-paul)
- Bug #43: Fixed `yii\sphinx\QueryBuilder::buildWithin()` does not define sort order for `SORT_ASC` (klimov-paul)
- Enh #11: `yii\sphinx\ActiveDataProvider` now disables `yii\data\Pagination::validatePage` automatically if `yii\sphinx\Query::showMeta` is set (klimov-paul)
- Enh #11: `yii\sphinx\ActiveDataProvider` now disables `yii\data\Pagination::validatePage` automatically if `yii\sphinx\Query::showMeta` is set (klimov-paul)
- Enh #17: Using total_found instead of total in `yii\sphinx\ActiveDataProvider::prepareTotalCount` (lmuzinic)
- Enh #29: Added `yii\sphinx\Command` automatically skips `null` values while inserting data (klimov-paul)


2.0.4 May 10, 2015
------------------

- Enh: Fetching 'SHOW META' info added to `yii\sphinx\Query` (klimov-paul)
- Enh #2053: Added fixture support via `yii\sphinx\ActiveFixture` (klimov-paul)
- Enh #5234: Facets fetching added to `yii\sphinx\Query` (klimov-paul)


2.0.3 March 01, 2015
--------------------

- Bug #7198: `yii\sphinx\Query` no longer attempts to call snippets for the empty query result set (Hrumpa)


2.0.2 January 11, 2015
----------------------

- Bug #6621: Creating sub query at `yii\sphinx\Query::queryScalar()` fixed (klimov-paul)


2.0.1 December 07, 2014
-----------------------

- Bug #5601: Simple conditions in Query::where() and ActiveQuery::where() did not allow `yii\db\Expression` to be used as the value (cebe, stevekr)
- Bug #5634: Fixed `yii\sphinx\QueryBuilder` does not support comparison operators (>,<,>= etc) in where specification (klimov-paul)
- Bug #6164: Added missing support for `yii\db\Exression` to QueryBuilder `LIKE` conditions (cebe)
- Enh #5223: Query builder now supports selecting sub-queries as columns (qiangxue)


2.0.0 October 12, 2014
----------------------

- Enh #5211: `yii\sphinx\Query` now supports 'HAVING' (klimov-paul)


2.0.0-rc September 27, 2014
---------------------------

- Bug #3668: Escaping of the special characters at 'MATCH' statement added (klimov-paul)
- Bug #4018: AR relation eager loading does not work with db models (klimov-paul)
- Bug #4375: Distributed indexes support provided (klimov-paul)
- Bug #4830: `ActiveQuery` instance reusage ability granted (klimov-paul)
- Enh #3520: Added `unlinkAll()`-method to active record to remove all records of a model relation (NmDimas, samdark, cebe)
- Enh #4048: Added `init` event to `ActiveQuery` classes (qiangxue)
- Enh #4086: changedAttributes of afterSave Event now contain old values (dizews)
- Enh: Added support for using sub-queries when building a DB query with `IN` condition (qiangxue)
- Chg #2287: Split `yii\sphinx\ColumnSchema::typecast()` into two methods `phpTypecast()` and `dbTypecast()` to allow specifying PDO type explicitly (cebe)


2.0.0-beta April 13, 2014
-------------------------

- Bug #1993: afterFind event in AR is now called after relations have been populated (cebe, creocoder)
- Bug #2160: SphinxQL does not support `OFFSET` (qiangxue, romeo7)
- Enh #1398: Refactor ActiveRecord to use BaseActiveRecord class of the framework (klimov-paul)
- Enh #2002: Added filterWhere() method to yii\spinx\Query to allow easy addition of search filter conditions by ignoring empty search fields (samdark, cebe)
- Enh #2892: ActiveRecord dirty attributes are now reset after call to `afterSave()` so information about changed attributes is available in `afterSave`-event (cebe)
- Enh #3964: Gii generator for Active Record model added (klimov-paul)
- Chg #2281: Renamed `ActiveRecord::create()` to `populateRecord()` and changed signature. This method will not call instantiate() anymore (cebe)
- Chg #2146: Removed `ActiveRelation` class and moved the functionality to `ActiveQuery`.
             All relational queries are now directly served by `ActiveQuery` allowing to use
             custom scopes in relations (cebe)


2.0.0-alpha, December 1, 2013
-----------------------------

- Initial release.
