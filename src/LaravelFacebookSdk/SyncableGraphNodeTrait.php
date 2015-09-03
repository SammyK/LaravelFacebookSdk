<?php namespace SammyK\LaravelFacebookSdk;

use Illuminate\Database\Eloquent\Model;
use Facebook\GraphNodes\GraphObject;
use Facebook\GraphNodes\GraphNode;

trait SyncableGraphNodeTrait
{
    /*
     * List of Facebook field names and their corresponding
     * column names as they exist in the local database.
     *
     * protected static $graph_node_field_aliases = [];
     */

    /*
     * List of Facebook field names that should be inserted
     * into the local database.
     *
     * protected static $graph_node_fillable_fields = [];
     */

    /*
     * The format the \DateTime instances will be converted
     * to before inserting into the database.
     *
     * protected static $graph_node_date_time_to_string_format = 'Y-m-d H:i:s';
     */

    /**
     * Inserts or updates the Graph node to the local database
     *
     * @param array|GraphObject|GraphNode $data
     *
     * @return Model
     *
     * @throws \InvalidArgumentException
     */
    public static function createOrUpdateGraphNode($data)
    {
        // @todo this will be GraphNode soon
        if ($data instanceof GraphObject || $data instanceof GraphNode) {
            $data = array_dot($data->asArray());
        }

        $data = static::convertGraphNodeDateTimesToStrings($data);

        if (! isset($data['id'])) {
            throw new \InvalidArgumentException('Graph node id is missing');
        }

        $attributes = [static::getGraphNodeKeyName() => $data['id']];

        $graph_node = static::firstOrNewGraphNode($attributes);

        static::mapGraphNodeFieldNamesToDatabaseColumnNames($graph_node, $data);

        $graph_node->save();

        return $graph_node;
    }

    /**
     * Like static::firstOrNew() but without mass assignment
     *
     * @param array $attributes
     *
     * @return Model
     */
    public static function firstOrNewGraphNode(array $attributes)
    {
        if (is_null($facebook_object = static::firstOrNew($attributes))) {
            $facebook_object = new static();
        }

        return $facebook_object;
    }

    /**
     * Convert a Graph node field name to a database column name
     *
     * @param string $field
     *
     * @return string
     */
    public static function fieldToColumnName($field)
    {
        $model_name = get_class(new static());
        if (property_exists($model_name, 'graph_node_field_aliases')
            && isset(static::$graph_node_field_aliases[$field])) {
            return static::$graph_node_field_aliases[$field];
        }

        return $field;
    }

    /**
     * Get db column name of primary Graph node key
     *
     * @return string
     */
    public static function getGraphNodeKeyName()
    {
        return static::fieldToColumnName('id');
    }

    /**
     * Map Graph-node field names to local database column name
     *
     * @param Model $object
     * @param array $fields
     */
    public static function mapGraphNodeFieldNamesToDatabaseColumnNames(Model $object, array $fields)
    {
        foreach ($fields as $field => $value) {
            if (static::graphNodeFieldIsWhiteListed(static::fieldToColumnName($field))) {
                $object->{static::fieldToColumnName($field)} = $value;
            }
        }
    }

    /**
     * Convert instances of \DateTime to string
     *
     * @param array $data
     * @return array
     */
    private static function convertGraphNodeDateTimesToStrings(array $data)
    {
        $date_format = 'Y-m-d H:i:s';
        $model_name = get_class(new static());
        if (property_exists($model_name, 'graph_node_date_time_to_string_format')) {
            $date_format = static::$graph_node_date_time_to_string_format;
        }

        foreach ($data as $key => $value) {
            if ($value instanceof \DateTime) {
                $data[$key] = $value->format($date_format);
            }
        }

        return $data;
    }

    /**
     * Check a key for fillableness
     *
     * @param string $key
     * @return boolean
     */
    private static function graphNodeFieldIsWhiteListed($key)
    {
        $model_name = get_class(new static());
        if (!property_exists($model_name, 'graph_node_fillable_fields')) {
            return true;
        }

        return in_array($key, static::$graph_node_fillable_fields);
    }
}
