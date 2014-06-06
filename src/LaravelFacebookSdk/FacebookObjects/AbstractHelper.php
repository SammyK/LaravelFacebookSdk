<?php namespace SammyK\LaravelFacebookSdk\FacebookObjects;

use SammyK\LaravelFacebookSdk\LaravelFacebookSdk;

abstract class AbstractHelper
{
    /**
     * @var \SammyK\LaravelFacebookSdk\LaravelFacebookSdk
     */
    protected $facebook;

    /**
     * The Facebook object id that we're working with
     *
     * @var int
     */
    protected $facebook_object_id = 0;

    /**
     * @param \SammyK\LaravelFacebookSdk\LaravelFacebookSdk
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
