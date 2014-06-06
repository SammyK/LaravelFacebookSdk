<?php namespace SammyK\LaravelFacebookSdk\FacebookObjects;

class UserHelper extends AbstractHelper
{
    /**
     * Determines if the user canceled the authentication request
     *
     * @return bool
     */
    public function canceledRequest()
    {
        return isset($_GET['error_reason'])
            && $_GET['error_reason'] === 'user_denied';
    }

    /**
     * Get the logged in user's profile picture
     *
     * @param int $user_id
     * @param mixed $params
     * @return string
     */
    public function photo($user_id = null, $params = null)
    {
        $url_append = '';

        // Build params
        if (isset($params))
        {
            // Auto-detect quick custom square-sized photo
            if (is_numeric($params))
            {
                $params = [
                    'height' => $params,
                    'width' => $params,
                    ];
            }

            // If it ain't an array, we can't used it
            if (is_array($params))
            {
                $url_append = '?' . http_build_query($params, null, '&');
            }
        }

        $user_id = $user_id ?: $this->facebook_object_id;

        return 'https://graph.facebook.com/' . $user_id . '/picture' . $url_append;
    }

}
