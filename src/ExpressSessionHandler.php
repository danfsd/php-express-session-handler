<?php

namespace danfsd;

/**
 * Session Handler that talks the same language as your express-session.
 */
class ExpressSessionHandler extends \SessionHandler
{
  /** @var string */
  private $secret;

  /**
   * Constructor
   *
   * @param string $secret The secret defined in your express-session library.
   */
  public function __construct($secret)
  {
    $this->secret = $secret;
  }

  /**
   * Populate the Session with cookie metadata that express-session requires.
   *
   * @return void
   */
  public function populateSession()
  {
    if (!isset($_SESSION["cookie"])) {
      $cookie = session_get_cookie_params();

      // TODO: express-session and PHP have different behaviors regarding `_expires` and `originalMaxAge`. One stores in milliseconds other in seconds. We need to find a way to normalize it.
      $_SESSION["cookie"] = [
        "path" => $cookie["path"],
        "httpOnly" => $cookie["httponly"],
        "domain" => $cookie["domain"]
      ];
    }
  }

  /**
   * Verifies if Session ID that is stored on cookies are tampered.
   * {@inheritDoc}
   * After the session is opened, stores cookie information on `$_SESSION['cookie']`.
   *
   * @param string $save_path
   * @param string $session_name
   * @return bool
   */
  public function open($save_path, $session_name)
  {
    if (isset($_COOKIE[$session_name])) {
      // TODO: verify if it's tampered and if so regenerate session id
    }

    return parent::open($save_path, $session_name);
  }

  /**
   * Generates an express-session compatible Session ID
   *
   * @return string
   */
  public function create_sid()
  {
    $sessionId = parent::create_sid();
    $secretHash = str_replace("=", "", 
      base64_encode(
        hash_hmac("sha256", $sessionId, $this->secret, true)
      )
    );
    return "s:{$sessionId}.{$secretHash}";
  }

  /**
   * Transforms the Session ID that is stored on `$_COOKIE` into the Session ID used on Redis
   * 
   * @param string $id The Session ID that is stored on cookie.
   * @return string
   */
  private function id_mutator($id)
  {
    if (substr($id, 0, 2) === "s:") {
        $id = substr($id, 2);
    }
    $dot_pos = strpos($id, ".");
    if ($dot_pos !== false) {
      $hmac_in = substr($id, $dot_pos + 1);
      $id = substr($id, 0, $dot_pos);
    }
    return $id;
  }

  /**
   * Reads data from Redis Store.
   *
   * @param string $session_id The Session ID that is stored on cookie.
   * @return string
   */
  public function read($session_id)
  {
    $session_id = $this->id_mutator($session_id);
    $session_data = parent::read($session_id);
    return serialize(json_decode($session_data, true));
  }

  /**
   * Writes data on Redis Store.
   *
   * @param string $session_id The Session ID that is stored on cookie.
   * @param string $session_data The data to write into the Redis Store.
   * @return bool
   */
  public function write($session_id, $session_data)
  {
    $session_id = $this->id_mutator($session_id);
    return parent::write($session_id, json_encode(unserialize($session_data)));
  }
}
