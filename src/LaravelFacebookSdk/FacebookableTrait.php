<?php namespace SammyK\LaravelFacebookSdk;

use SammyK\FacebookQueryBuilder\GraphObject;

trait FacebookableTrait
{
    /*
     * List of Facebook field names and their corresponding
     * column names as they exist in the local database.
     *
     * protected static $facebook_field_aliases = [];
     */

    /**
     * Convert a Facebook field to a database column name
     *
     * @param string
     * @return string
     */
    public static function fieldToColumnName($field)
    {
        if (isset(static::$facebook_field_aliases[$field]))
        {
            return static::$facebook_field_aliases[$field];
        }
        return $field;
    }

    /**
     * Get column name of primary Facebook object key
     *
     * @return string
     */
    public static function getFacebookObjectKeyName()
    {
        return static::fieldToColumnName('id');
    }

    /**
     * Map Facebook-named data to local database-named data
     *
     * @param \Illuminate\Database\Eloquent\Model
     * @param array
     */
    public static function mapFacebookFieldsToObject($object, array $fields)
    {
        foreach ($fields as $field => $value)
        {
            $object->{static::fieldToColumnName($field)} = $value;
        }
    }

    /**
     * Inserts or updates the Facebook object to the local database
     *
     * @param array|\SammyK\FacebookQueryBuilder\GraphObject
     *
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws LaravelFacebookSdkException
     */
    public static function createOrUpdateFacebookObject($data)
    {
        if ($data instanceof GraphObject)
        {
            $data = $data->toArray();
        }

        if ( ! isset($data['id']))
        {
            throw new LaravelFacebookSdkException('Facebook object id is missing');
        }

        $attributes = [static::getFacebookObjectKeyName() => $data['id']];

        $facebook_object = static::firstOrNewFacebookObject($attributes);

        static::mapFacebookFieldsToObject($facebook_object, $data);

        $facebook_object->save();

        return $facebook_object;
    }

    /**
     * Like static::firstOrNew() but without mass assignment
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function firstOrNewFacebookObject(array $attributes)
    {
        if (is_null($facebook_object = static::firstByAttributes($attributes)))
        {
            $facebook_object = new static();
        }

        return $facebook_object;
    }
}
