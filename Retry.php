<?php
/**
 * Class Retry
 *
 * Helper to call a given callable multiple times if the first time it fails.
 * Allows you to define what a fail means, how many retries, and how much time between retries.
 *
 * Note that this is not async. The execution will be stopped here until all retries finish
 * or the execution is succesful.
 *
 */
class Retry {

    /**
     * How many times are we going to try?
     * Default is 1.
     * @var int
     */
    protected $times = 1;

    /**
     * How many seconds between retries.
     * Default is 1.
     * @var int
     */
    protected $delay = 1;

    /**
     * Function that we're going to execute.
     * @var callable
     */
    protected $fn = null;

    /**
     *
     * @var array
     */
    protected $args = array();

    /**
     * Callback to define if the execution was failed or not.
     * @var callable
     */
    protected $failed_if = 'is_wp_error';

    /**
     * Gives a Retry object. Needs to be initialized with a callable.
     * That callable is the function we're going to try to execute
     * and retry if it fails.
     *
     * @param callable $fn
     */
    public function __construct( callable $fn ) {
        $this->fn = $fn;
    }

    /**
     * Optional array of arguments to pass to the function
     * we're going to try to execute.
     *
     * @param array $args
     *
     * @return $this
     */
    public function with_args( $args ) {
        $this->args = $args;

        return $this;
    }

    /**
     * How many times are we going to retry?
     * Default is 1.
     *
     * @param $times
     *
     * @return $this
     */
    public function times( $times ) {
        $this->times = $times;

        return $this;
    }

    /**
     * How many seconds do we wait between retries?
     * Default is 1.
     *
     * @param $seconds
     *
     * @return $this
     */
    public function with_delay( $seconds ) {
        $this->delay = $seconds;

        return $this;
    }

    /**
     * How do we know if the execution failed?
     *
     * @param callable $fn Should recieve a single parameter $response and should
     *                     return a bool. True means the call was failed, false means
     *                     that it was succesful and we're good to go.
     *
     *
     * Default is is_wp_error
     *
     * @return $this
     */
    public function failed_if( callable $fn ) {
        $this->failed_if = $fn;

        return $this;
    }

    /**
     * Start the execution!
     *
     * @return mixed
     */
    public function go() {

        if ( empty( $this->failed_if ) ) {
            $this->failed_if( 'is_wp_error' );
        }

        $result = null;

        for ( $i = 0; $i < $this->times; $i ++ ) {

            $result = call_user_func_array( $this->fn, $this->args );
            $failed = call_user_func_array( $this->failed_if, array( $result ) );

            if ( ! $failed ) {
                return $result;
            }

            sleep( $this->delay );
        }

        return $result;

    }

    /**
     * Just a wrapper around the instance creation.
     * Pure syntax sugar.
     *
     * @param callable $fn
     *
     * @return \MacMillan\Retry
     */
    public static function calling( callable $fn ) {
        return new self( $fn );
    }

}
