<?php namespace Ninelivesevelopment\LaravelFacebookSdk\FacebookObjects;

use Ninelivesevelopment\LaravelFacebookSdk\LaravelFacebookSdk;

abstract class AbstractHelper
{
    /**
     * @var \Ninelivesevelopment\LaravelFacebookSdk\LaravelFacebookSdk
     */
    protected $facebook;

    /**
     * The Facebook object id that we're working with
     *
     * @var int
     */
    protected $facebook_object_id = 0;

    /**
     * @param \Ninelivesevelopment\LaravelFacebookSdk\LaravelFacebookSdk
     * @param int $id
     */
    public function __construct(LaravelFacebookSdk $facebook, $id = 0)
    {
        $this->facebook = $facebook;
        $this->facebook_object_id = $id;
    }

    /**
     * Sets the Facebook object id
     *
     * @param int
     * @return null
     */
    public function setFacebookObjectId($id)
    {
        $this->facebook_object_id = $id;
    }

    /**
     * Gets the Facebook object id
     *
     * @return int
     */
    public function getFacebookObjectId()
    {
        return $this->facebook_object_id;
    }

}
