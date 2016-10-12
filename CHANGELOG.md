# CHANGELOG

## 3.4.1 - October 12, 2016

- Bumped the Graph API version in the config to [v2.8](https://developers.facebook.com/docs/apps/changelog).

## 3.4.0 - August 25, 2016

- Add support for Laravel 5.3

## 3.3.3 - August 22, 2016

- Updated the Facebook PHP SDK package name from `facebook/php-sdk-v4` to the new name `facebook/graph-sdk`.

## 3.3.2 - August 6, 2016

- Bumped the Graph API version in the config [v2.7](https://developers.facebook.com/docs/apps/changelog).

## 3.3.1 - April 12, 2016

- Bumped the Graph API version in the config [v2.6](https://developers.facebook.com/docs/apps/changelog).

## 3.3.0 - February 9, 2016

- Added support for Lumen and Laravel 5.2.


## 3.2.1 - February 8, 2016

- Updated the config and url type hints to reference interfaces instead of concrete implementations.


## 3.2.0 - November 12, 2015

- Added ability to create new instances of `LaravelFacebookSdk` with a [different app settings](https://github.com/SammyK/LaravelFacebookSdk/tree/3.0#working-with-multiple-apps).


## 3.1.0 - September 3, 2015

- Added [fillable fields](https://github.com/SammyK/LaravelFacebookSdk/tree/3.0#specifying-fillable-fields) feature.
- Added [array dot notation to field mapping](https://github.com/SammyK/LaravelFacebookSdk/tree/3.0#nested-field-mapping).
- Added feature to convert [`DateTime` to string format](https://github.com/SammyK/LaravelFacebookSdk/tree/3.0#date-formats).


## 3.0.2 - July 21, 2015

- Removed `@dev` flag from Facebook PHP SDK since v5 was released. Yay!


## 3.0.1 - July 8, 2015

- Updated the Graph API version references to latest v2.4.
- Tiny fix in tests when strict errors are displayed.


## 3.0.0 - June 23, 2015

- Added support for Laravel 5.1.


## 2.0.2 - May 14, 2015

- Added support for the Facebook PHP SDK's `GraphNode` entity.


## 2.0.1 - May 8, 2015

- Updated the version alias of the Facebook PHP SDK v4.1 to v5 which is the same version but v5 is now following SemVer.


## 2.0.0 - February 13, 2015

- Upgraded to work on Laravel 5! Yay!
- Upgraded to use the latest Facebook PHP SDK ~~v4.1~~ **v5**.
- Removed the [Facebook Query Builder](https://github.com/SammyK/FacebookQueryBuilder) dependency.
- Removed the migration stub since there's only one column we need to add for most cases.
- Removed the config, route & view for the `channel.html` since that seems to be deprecated.
- Renamed `FacebookableTrait` to `SyncableGraphNodeTrait` since the Graph API returns nodes, not objects.
    - Renamed `createOrUpdateFacebookObject()` to `createOrUpdateGraphNode()` since that makes more sense on Facebook's domain.
    - Renamed the `$facebook_field_aliases` static property to `$graph_node_field_aliases`.
- Added support for re-requests and re-authentications.
- Removed `LaravelFacebookSdkException` to let all the native PHP SDK's exceptions fall through.
- Removed all the weird authentication crap that didn't make any sense.
- Bound the package to the IoC container as `SammyK\LaravelFacebookSdk\LaravelFacebookSdk` instead of the old `facebook-query-builder`.


## 1.1.1 - December 11, 2014

- Updated branching model in preparation of upgrade to Facebook PHP SDK ~~v4.1~~ **v5**.


## 1.1.0 - July 16, 2014

- Adjusted tagging to work according to [semver](http://semver.org/).
- Updated version of [Facebook Query Builder](https://github.com/SammyK/FacebookQueryBuilder) for semver fix.
- Added CHANGELOG.


## 1.0.0 - June 6, 2014

- Initial release. Hello world!
