<?php

/**
 * Trait TourGuideLocalTrait
 * 
 * Provides shared utility methods for use in Tour Guide-related controllers,
 * handling AJAX requests, permissions, session alerts, redirection, view loading,
 * and response transformation.
 */
trait TourGuideLocalTrait
{
    /**
     * Checks if the current request is an AJAX request.
     * 
     * @return bool Returns true if the request is made via AJAX, otherwise false.
     */
    public function isAJAX()
    {
        return $this->input->is_ajax_request();
    }

    /**
     * Checks if the current user has permission to perform a specific action.
     * 
     * @param string $action The action to check permissions for (e.g., 'create', 'edit').
     * @return bool Returns true if the user has permission or is an admin, otherwise false.
     */
    public function staffCan($action)
    {
        return staff_can($action, 'tour_guide', '');
    }

    /**
     * Sets a session-based alert message to be shown to the user.
     * 
     * @param string $type The type of alert (e.g., 'success', 'error').
     * @param string $message The alert message content.
     */
    public function setAlert($type, $message)
    {
        // Set a flash data message in the session
        return set_alert($type, $message);
    }

    /**
     * Redirects the user to the specified URI.
     * 
     * @param string $uri The destination URI for redirection.
     */
    public function redirect($uri)
    {
        // Redirect to the given URI
        redirect($uri);
    }

    /**
     * Retrieves the current URI string of the request.
     * 
     * @return string The URI of the current request.
     */
    public function uriString()
    {
        // Return the URI string of the current request
        return uri_string();
    }

    /**
     * Loads and renders a specific view file with optional data.
     * 
     * @param string $view The view file to load (relative to the views directory).
     * @param array $data Optional array of data to be passed to the view.
     * @return string Rendered HTML content of the view.
     */
    public function loadView($view, $data = [])
    {
        return $this->load->view($view, $data);
    }

    /**
     * Ensures that the current user has permission to perform a specific action.
     * 
     * Redirects to the "forbidden" page if the user lacks the necessary permission.
     * 
     * @param string $action The action to verify permissions for.
     */
    public function ensureStaffCan($action)
    {
        // Redirect to "forbidden" if the user cannot perform the action
        if (!$this->staffCan($action)) {
            access_denied('tour_guide');
            exit; // Terminate script execution after redirection
        }
    }

    /**
     * Initializes a response transformer that modifies the structure of response data.
     * 
     * The transformer appends a 'status' field to the response based on whether the
     * operation was successful or not.
     */
    public function initTransformer()
    {
        // Define a closure to transform the response data
        $response_transformer = function ($data) {
            $data['status'] = $data['success'] ? 'success' : 'danger';
            return $data;
        };

        // Initialize the transformer with the defined response transformer
        $this->initTrait($response_transformer);
    }
}