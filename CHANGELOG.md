# CHANGELOG


## 2.0.0 - February 13, 2015

- Upgraded to work on Laravel 5! Yay!
- Upgraded to use the latest Facebook PHP SDK v4.1.
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

- Updated branching model in preparation of upgrade to Facebook PHP SDK v4.1.


## 1.1.0 - July 16, 2014

- Adjusted tagging to work according to [semver](http://semver.org/).
- Updated version of [Facebook Query Builder](https://github.com/SammyK/FacebookQueryBuilder) for semver fix.
- Added CHANGELOG.


## 1.0.0 - June 6, 2014

- Initial release. Hello world!
