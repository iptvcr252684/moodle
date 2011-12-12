<?php

defined('MOODLE_INTERNAL') || die();

require_once('Auth/OpenID.php');
require_once('Auth/OpenID/Server.php');
require_once('Auth/OpenID/FileStore.php');

require_once($CFG->dirroot . '/local/openid_idp/extensions/sreg.php');

class openid_helper {
    private function __construct() {
        global $CFG;
        $url = $this->make_url();
        $this->openidserver = new Auth_OpenID_Server(new Auth_OpenID_FileStore($CFG->dataroot.'/openid'),
                                                     $url->out(true));
    }

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new openid_helper;
        }
        return self::$instance;
    }

    public function make_url(array $options = null) {
        if (isset($options['action']) && $options['action'] === 'request') {
            unset($options['action']);
        }
        return new moodle_url('/local/openid_idp/index.php', $options);
    }

    public function do_request() {
        global $SESSION, $PAGE, $OUTPUT, $CFG, $USER, $DB;

        header('X-XRDS-Location: ' . $this->make_url(array('action' => 'idpXrds')));

        $request = $this->openidserver->decodeRequest();

        if (!$request) {
            if (isset($SESSION->openid_request)) {
                // no current request -- see if we have a pending request
                $request = unserialize($SESSION->openid_request);
            } else {
                require($CFG->dirroot . '/local/openid_idp/about.php');
                return;
            }
        } else {
            $SESSION->openid_request = serialize($request);
        }

        if ($request->mode === 'checkid_immediate'
            || $request->mode === 'checkid_setup') {

            // check if site is trusted, first sitewide, then by the current user
            $url = $request->trust_root;
            $trusted = $DB->get_record('local_openid_idp_trusted_rps', array('url' => $url, 'userid' => 0));
            if (!$trusted && isset($USER->id)) {
                $trusted = $DB->get_record('local_openid_idp_trusted_rps', array('url' => $url, 'userid' => $USER->id));
            }

            if ($trusted) {
                // site is trusted, so don't need to ask user for confirmation
                require_login(null, false);
                require_capability('local/openid_idp:logintoremote', get_context_instance(CONTEXT_SYSTEM));

                //check requested identity (if specified)
                $identity = $this->make_url(array('action' => 'user', 'id' => $USER->id))->out(false);
                if (!$request->idSelect() && $request->identity != $identity) {
                    print_error('incorrect_identity', 'local_openid_idp');
                }

                $options = unserialize($trusted->options);

                $response =& $request->answer(true, null, $identity);

                $extensions = $this->get_extensions();
                foreach ($extensions as $extension) {
                    if (function_exists('trust_request_form_response_'.$extension)) {
                        call_user_func('trust_request_form_response_'.$extension, $request, $response, $options, $USER);
                    }
                }

                $this->send_response($response);
                return;
            }

            if ($request->idSelect()) {
                // Perform IDP-driven identifier selection
                if ($request->mode == 'checkid_immediate') {
                    $response =& $request->answer(false);
                } else {
                    require($CFG->dirroot . '/local/openid_idp/trust_request.php');
                    return;
                }
            } else if ((!$request->identity) &&
                       (!$request->idSelect())) {
                print_error('no_identifier', 'local_openid_idp');
            } else if ($request->immediate) {
                $response =& $request->answer(false, $CFG->wwwroot);
            } else {
                require($CFG->dirroot . '/local/openid_idp/trust_request.php');
                return;
            }
        } else {
            $response =& $this->openidserver->handleRequest($request);
        }

        $this->send_response($response);
    }

    public function do_user() {
        global $OUTPUT;

        $userid = required_param('id', PARAM_INT);

        header('X-XRDS-Location: ' . $this->make_url(array('action' => 'userXrds', 'id' => $userid)));

        echo $OUTPUT->header();
        // FIXME:
        echo 'This is the OpenID page for the user with ID '.$userid;
        echo $OUTPUT->footer();
    }

    /**
     * Print XRDS info
     */
    public function do_idpXrds() {
        header('Content-type: application/xrds+xml');
        $openid2 = Auth_OpenID_TYPE_2_0_IDP;
        $ax = Auth_OpenID_AX_NS_URI;
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<xrds:XRDS
    xmlns:xrds=\"xri://\$xrds\"
    xmlns=\"xri://\$xrd*(\$v*2.0)\">
  <XRD>
    <Service priority=\"0\">
      <Type>$openid2</Type>
      <Type>$ax</Type>
      <URI>{$this->make_url()}</URI>
    </Service>
  </XRD>
</xrds:XRDS>";
    }

    public function do_userXrds() {
        header('Content-type: application/xrds+xml');
        $userid = required_param('id', PARAM_INT);
        $openid2 = Auth_OpenID_TYPE_2_0;
        $openid11 = Auth_OpenID_TYPE_1_1;
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<xrds:XRDS
    xmlns:xrds=\"xri://\$xrds\"
    xmlns=\"xri://\$xrd*(\$v*2.0)\">
  <XRD>
    <Service priority=\"0\">
      <Type>$openid2</Type>
      <Type>$openid11</Type>
      <URI>{$this->make_url()}</URI>
      <LocalID>{$this->make_url(array('action' => 'user', 'id' => $userid))}</URI>
    </Service>
  </XRD>
</xrds:XRDS>";
    }

    public function send_response($response) {
        $webresponse =& $this->openidserver->encodeResponse($response);

        if ($webresponse->code != AUTH_OPENID_HTTP_OK) {
            header(sprintf("HTTP/1.1 %d ", $webresponse->code),
                   true, $webresponse->code);
        }

        foreach ($webresponse->headers as $k => $v) {
            header("$k: $v");
        }

        header('Connection: close');
        print $webresponse->body;
    }

    public function get_extensions() {
        global $CFG;

        static $extensions = null;

        if ($extensions === null) {
            $extensions = array();
            if ($handle = opendir($CFG->dirroot . '/local/openid_idp/extensions')) {
                /* This is the correct way to loop over the directory. */
                while (false !== ($file = readdir($handle))) {
                    if (substr($file, -4) === '.php') {
                        require_once($CFG->dirroot . '/local/openid_idp/extensions/' . $file);
                        $extensions[] = substr($file, 0, -4);
                    }
                }

                closedir($handle);
            }
        }

        return $extensions;
    }
}
