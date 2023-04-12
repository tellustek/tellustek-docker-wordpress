<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Library\Uri\Uri;

defined('ADMINTOOLSINC') or die;

/**
 * Allows users to "rename" their administrator directory.
 */
class AtsystemFeatureCustomadminfolder extends AtsystemFeatureAbstract
{
	protected $loadOrder = 40;
	private $auth_cookie_expired;
	private $old_admin = false;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		$folder = $this->cparams->getValue('adminlogindir');

		return ($folder != '');
	}

	/**
	 * Attaches to our custom hook, so we can add more functions to WordPress hooks
	 */
	public function onCustomHooks()
	{
		add_action('auth_cookie_expired', array( $this, 'auth_cookie_expired'));
		add_action('init'               , array( $this, 'handleRequest'), 1000);
		add_action('login_init'         , array( $this, 'blockBackendLogin'));
		add_action('plugins_loaded'     , array( $this, 'plugins_loaded'), 11);

		// add_filter('body_class', array( $this, 'remove_admin_bar' ));
		add_filter('loginout'           , array( $this, 'updateLoginUrl' ));
		add_filter('wp_redirect'        , array( $this, 'updateLoginUrl'));
		add_filter('lostpassword_url'   , array( $this, 'updateLoginUrl'));
		add_filter('site_url'           , array( $this, 'updateLoginUrl'));

		add_filter('retrieve_password_message'  , array( $this, 'updateLoginUrl'));
		add_filter('comment_moderation_text'    , array( $this, 'updateText'));

		remove_action('template_redirect'  , 'wp_redirect_admin_locations', 1000);
	}

	public function auth_cookie_expired()
	{
		static $alreadyRunning = false;

		if ($alreadyRunning)
		{
			return;
		}

		$alreadyRunning            = true;
		$this->auth_cookie_expired = true;

		wp_clear_auth_cookie();

		$alreadyRunning = false;
	}

	/**
	 * Handles the incoming request and displays the original login URL, if requested
	 */
	public function handleRequest()
	{
		$admindir         = $this->cparams->getValue('adminlogindir');
		$post_logout_slug = $this->cparams->getValue('post_logout_slug');
		$uri              = Uri::getInstance();

		$this->allowOrBlock();

		$login_path                = site_url($admindir, 'relative');
		$login_path_trailing_slash = site_url($admindir.'/', 'relative');

		// This is not the page we're interested into, let's bail out
		if ($uri->getPath() !== $login_path && $uri->getPath() !== $login_path_trailing_slash)
		{
			return;
		}

		$action = $this->input->getCmd('action', '');

		if (!is_user_logged_in())
		{
			//Add the login form
			status_header( 200 );

			//don't allow domain mapping to redirect
			if ( defined( 'DOMAIN_MAPPING' ) && DOMAIN_MAPPING == 1 )
			{
				remove_action( 'login_head', 'redirect_login_to_orig' );
			}

			if ( ! function_exists( 'login_header' ) )
			{
				include( ABSPATH . 'wp-login.php' );
				exit;
			}
		}
		elseif
		(!$action ||
			(
				!in_array($action, array('logout', 'postpass')) && ($post_logout_slug && $action != $post_logout_slug)
			)
		)
		{
			//Just redirect them to the dashboard (for logged in users)
			if ( $this->auth_cookie_expired === false )
			{
				wp_redirect( get_admin_url() );
				exit();
			}
		}
		elseif (
			$action &&
			(
				$action == 'postpass' || ( $post_logout_slug && ($action == $post_logout_slug))
			)
		)
		{
			status_header( 200 );

			//include the login page where we need it
			if (!function_exists('login_header'))
			{
				include( ABSPATH . '/wp-login.php' );
				exit;
			}

			// Take them back to the page if we need to
			if (isset( $_SERVER['HTTP_REFERRER']))
			{
				wp_redirect(sanitize_text_field($_SERVER['HTTP_REFERRER']));
				exit();
			}
		}
	}

	/**
	 * Blocks the original login page.
	 */
	public function blockBackendLogin()
	{
		if (strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ))
		{
			$this->old_admin = true;

			$this->exceptionsHandler->logAndAutoban('admindir');

			wp_redirect(site_url());
		}
	}

	/**
	 * Manually logs out the user
	 */
	public function plugins_loaded()
	{
		if (is_user_logged_in() && ($this->input->getCmd('action', '') == 'logout'))
		{
			check_admin_referer('log-out');
			wp_logout();

			$redirect_to = ! empty($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : 'wp-login.php?loggedout=true';
			wp_safe_redirect( $redirect_to );
			exit();
		}
	}

	/**
	 * Updates the original URL to replace any instance to wp-login.php with the new secret param
	 *
	 * @param   string  $url
	 *
	 * @return  string
	 */
	public function updateLoginUrl($url)
	{
		return str_replace('wp-login.php', $this->cparams->getValue('adminlogindir'), $url);
	}

	/**
	 * Updates any text to replace any admin reference with the new secret param
	 *
	 * @param   string  $message
	 *
	 * @return  string
	 */
	public function updateText($message)
	{
		preg_match_all("#(https?://((.*)wp-admin(.*)))#", $message, $urls);

		if (isset( $urls ) && is_array($urls) && isset( $urls[0] ))
		{
			foreach ( $urls[0] as $url )
			{
				$message = str_replace(trim($url), wp_login_url(trim($url)), $message);
			}
		}

		return $message;
	}

	/**
	 * Helper function use to block or allow incoming requests
	 */
	private function allowOrBlock()
	{
		$uri       = Uri::getInstance();
		$home      = Uri::getInstance(site_url());
		$register  = $this->cparams->getValue('customregister', 'wp-register.php');
		$home_root = '/';

		if ($home->getPath())
		{
			$home_root = $home->getPath();
		}

		if (get_site_option('users_can_register') == 1 && $_SERVER['REQUEST_URI'] == $home_root . $register)
		{
			wp_redirect( wp_login_url() . '?action=register' );
			exit;
		}

		// No registration but we're requesting a signup page
		if (get_site_option( 'users_can_register' ) == false)
		{
			if (strpos($uri->toString(), 'wp-register.php' ) || strpos($uri->toString(), 'wp-signup.php' ))
			{
				// Record we previously hit the "old admin" WP page
				$this->old_admin = true;
				$this->exceptionsHandler->logAndAutoban('admindir');

				$this->handleDeniedRequest();
			}
		}

		// User not logged in, trying to reach login page
		if (strpos($uri->toString(), 'wp-login.php' ) && is_user_logged_in() !== true)
		{
			// Record we previously hit the "old admin" WP page
			$this->old_admin = true;
			$this->exceptionsHandler->logAndAutoban('admindir');

			$this->handleDeniedRequest();
		}

		// Do not block if we're doing an admin-ajax request
		if (strpos( $_SERVER['REQUEST_URI'], 'admin-ajax.php' ) !== false)
		{
			return;
		}

		// Trying to access the admin area but it's not a logged in user, block
		if (is_admin() && is_user_logged_in() !== true )
		{
			// Record we previously hit the "old admin" WP page
			$this->old_admin = true;
			$this->exceptionsHandler->logAndAutoban('admindir');

			$this->handleDeniedRequest();
		}

		// Do not block if our cookies expired
		if ($this->auth_cookie_expired !== false)
		{
			return;
		}

		// We're using a custom page for registration...
		if ($register != 'wp-register.php')
		{
			// ... but we requested the default ones
			if (
				strpos($uri->toString(), 'wp-register.php') !== false ||
				strpos($uri->toString(), 'wp-signup.php') !== false
			)
			{
				// Record we previously hit the "old admin" WP page
				$this->old_admin = true;
				$this->exceptionsHandler->logAndAutoban('admindir');

				$this->handleDeniedRequest();
			}

			// ... or we try to redirect to the customize file in the admin section
			$queryArgs = $uri->getQuery(true);

			if (isset($queryArgs['redirect_to']) && strpos($queryArgs['redirect_to'], 'wp-admin/customize.php') !== false)
			{
				// Record we previously hit the "old admin" WP page
				$this->old_admin = true;
				$this->exceptionsHandler->logAndAutoban('admindir');

				$this->handleDeniedRequest();
			}
		}

		// If we're here, it means we're good to go
	}

	/**
	 * We tried to reach a protected page without success, perform the redirect or show a 403 error accordingly to the settings
	 */
	private function handleDeniedRequest()
	{
		$action = $this->cparams->getValue('adminlogindir_action');

		// Action == 2, redirect the site's homepage
		if ($action == 2)
		{
			if (wp_redirect( get_home_url(), 301 ))
			{
				exit;
			}

			// If for some reasons the above redirect fails, fallback to a 403 error
			wp_die( __( 'This has been disabled.', 'admintoolswp' ), 403 );
		}

		// Show a 404 error page
		if($action == 3)
		{
			global $wp_query;

			$wp_query->set_404();

			status_header( 404 );
			get_template_part( 404 );

			exit();
		}

		// In any other case display the 403 error
		wp_die( __( 'This has been disabled.', 'admintoolswp' ), 403 );
	}
}
