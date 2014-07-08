Retry
=====

https://www.youtube.com/watch?v=dQw4w9WgXcQ


Small helper to call a given callable multiple times if the first time it fails.
Allows you to define what a fail means, how many retries, and how much time between retries.

Note that this is not async. The execution will be stopped here until all retries finish or the execution is succesful.

Sample usage:

```
$response = Retry::calling( 'wp_remote_post' )
		                 ->with_args( array( $url, $args ) )
		                 ->times( 3 )
		                 ->with_delay( 1 )
		                 ->failed_if( 'is_wp_error' )
		                 ->go();
```
