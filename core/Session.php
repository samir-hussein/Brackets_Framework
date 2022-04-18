<?php

namespace App;

class Session
{
    public function __construct()
    {
        $sessions = fetchFile('../storage/sessions/sessions.php');
        $flash_messages = $sessions['flash'][sha1(request()->ip())] ?? [];

        if ($flash_messages) {
            // mark flash sessions as expired
            foreach ($flash_messages as $key => &$message) {
                $message['exp'] = true;
            }
            $sessions['flash'][sha1(request()->ip())] = $flash_messages;
        }

        // remove expired sessions
        $user_sessions = $sessions['sessions'][sha1(request()->ip())] ?? [];
        if ($user_sessions) {
            foreach ($user_sessions as $key => $val) {
                if ($val['exp'] < time()) {
                    unset($user_sessions[$key]);
                }
            }
            $sessions['sessions'][sha1(request()->ip())] = $user_sessions;
        }
        file_put_contents('../storage/sessions/sessions.php', "<?php\nreturn " . var_export($sessions, true) . ";");
    }

    public function __destruct()
    {
        $sessions = fetchFile('../storage/sessions/sessions.php');
        $flash_messages = $sessions['flash'][sha1(request()->ip())] ?? [];
        // remove flash sessions that marked as expired
        if ($flash_messages) {
            foreach ($flash_messages as $key => $message) {
                if ($message['exp']) {
                    unset($flash_messages[$key]);
                }
            }
            $sessions['flash'][sha1(request()->ip())] = $flash_messages;
        }
        file_put_contents('../storage/sessions/sessions.php', "<?php\nreturn " . var_export($sessions, true) . ";");
    }

    /**
     * Set sessions
     *
     * @param string $key
     * @param string|array $value
     * @return void
     */
    public static function put(string $key, string|array $value)
    {
        $sessions = fetchFile('../storage/sessions/sessions.php');
        $temp['value'] = $value;
        $temp['exp'] = time() + (config('SESSION_LIFETIME') * 60);
        $sessions['sessions'][sha1(request()->ip())][$key] = $temp;
        file_put_contents('../storage/sessions/sessions.php', "<?php\nreturn " . var_export($sessions, true) . ";");
    }

    /**
     * Set flash session
     *
     * @param string $key
     * @param string|array $value
     * @return void
     */
    public static function putFlash(string $key, string|array $value)
    {
        $sessions = fetchFile('../storage/sessions/sessions.php');
        $temp['value'] = $value;
        $temp['exp'] = false;
        $sessions['flash'][sha1(request()->ip())][$key] = $temp;
        file_put_contents('../storage/sessions/sessions.php', "<?php\nreturn " . var_export($sessions, true) . ";");
    }

    /**
     * Get session
     *
     * @param string $key
     * @return string|array|null
     */
    public static function get(string $key)
    {
        $sessions = fetchFile('../storage/sessions/sessions.php')['sessions'];
        $sessions = $sessions[sha1(request()->ip())][$key]['value'] ?? null;
        return $sessions;
    }

    /**
     * Get flash session
     *
     * @param string $key
     * @return string|array|null
     */
    public static function getFlash(string $key)
    {
        $sessions = fetchFile('../storage/sessions/sessions.php')['flash'];
        $sessions = $sessions[sha1(request()->ip())][$key]['value'] ?? null;
        return $sessions;
    }

    /**
     * Delete session
     *
     * @param string $key
     * @return void
     */
    public static function remove(string $key)
    {
        $sessions = fetchFile('../storage/sessions/sessions.php');
        unset($sessions['sessions'][sha1(request()->ip())][$key]);
        file_put_contents('../storage/sessions/sessions.php', "<?php\nreturn " . var_export($sessions, true) . ";");
    }

    /**
     * Get all sessions
     *
     * @return array
     */
    public static function all(): array
    {
        $sessions = fetchFile('../storage/sessions/sessions.php');
        foreach ($sessions as &$key) {
            if (isset($key[sha1(request()->ip())])) {
                foreach ($key[sha1(request()->ip())] as &$val) {
                    unset($val['exp']);
                }
            }
        }
        return [
            'sessions' => $sessions['sessions'][sha1(request()->ip())] ?? [],
            'flash' => $sessions['flash'][sha1(request()->ip())] ?? []
        ];
    }

    /**
     * destroy all sessions
     *
     * @return void
     */
    public static function destroy(): void
    {
        $sessions = fetchFile('../storage/sessions/sessions.php');
        if (isset($sessions['sessions'][sha1(request()->ip())])) {
            unset($sessions['sessions'][sha1(request()->ip())]);
        }
        if (isset($sessions['flash'][sha1(request()->ip())])) {
            unset($sessions['flash'][sha1(request()->ip())]);
        }
        file_put_contents('../storage/sessions/sessions.php', "<?php\nreturn " . var_export($sessions, true) . ";");
    }
}
