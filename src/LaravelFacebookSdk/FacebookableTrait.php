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
        if (property_exists(__CLASS__, 'facebook_field_aliases') && isset(static::$facebook_field_aliases[$field]))
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
     * @return \Illuminate\Database\Eloquent\Model|bool
     *
     * @throws LaravelFacebookSdkException
     */
    public static function createOrUpdateFacebookObject($data)
    {
        if ($data instanceof GraphObject)
        {
            $data = $data->toArray();
        }

        $data = static::flattenFacebookDataArray($data);
        $data = static::removeIgnoreKeysFromFacebookData($data);

        if ( ! isset($data['id']))
        {
            throw new LaravelFacebookSdkException('Facebook object id is missing');
        }

        $attributes = [static::getFacebookObjectKeyName() => $data['id']];

        $facebook_object = static::firstOrNewFacebookObject($attributes, $data);

        if ( ! $facebook_object)
        {
            return false;
        }

        $facebook_object->save();

        return $facebook_object;
    }

    /**
     * Like static::firstOrNew() but without mass assignment
     *
     * @param array $attributes
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model|bool
     */
    public static function firstOrNewFacebookObject(array $attributes, array $data = [])
    {
        $facebook_object = static::firstByAttributes($attributes);

        if ($facebook_object)
        {
            static::mapFacebookFieldsToObject($facebook_object, $data);
            if (method_exists(__CLASS__, 'facebookObjectWillUpdate'))
            {
                return static::facebookObjectWillUpdate($facebook_object);
            }

            return $facebook_object;
        }

        $facebook_object = new static();
        static::mapFacebookFieldsToObject($facebook_object, $data);
        if (method_exists(__CLASS__, 'facebookObjectWillCreate'))
        {
            return static::facebookObjectWillCreate($facebook_object);
        }

        return $facebook_object;
    }

    /**
     * Flattens an array of data from Graph with the path as the key
     *
     * @param array $data
     *
     * @return array
     */
    private static function flattenFacebookDataArray(array $data)
    {
        $query = http_build_query($data, null, '&');
        $params = explode('&', $query);
        $result = [];

        foreach ($params as $param) {
            list($key, $value) = explode('=', $param, 2);
            $result[urldecode($key)] = urldecode($value);
        }

        return $result;
    }

    /**
     * Removes any keys from Facebook that we want to ignore
     *
     * @param array $data
     *
     * @return array
     */
    private static function removeIgnoreKeysFromFacebookData(array $data)
    {
        if (property_exists(__CLASS__, 'facebook_ignore_fields') && is_array(static::$facebook_ignore_fields))
        {
            foreach (static::$facebook_ignore_fields as $key)
            {
                unset($data[$key]);
            }
        }

        return $data;
    }
}
