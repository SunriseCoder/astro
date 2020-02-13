<?php

if (!class_exists('Db')) {
    include $_SERVER["DOCUMENT_ROOT"].'/utils/db.php';
}

if (!class_exists('Utils')) {
    include $_SERVER["DOCUMENT_ROOT"].'/utils/utils.php';
}

class Permission {
    // Administration
    const AdminMenuVisible = 'AdminMenuVisible';

    // Customer
    const AnswerAsCustomer = 'AnswerAsCustomer';

    public $id;
    public $code;
}

class PermissionDao {
    public static function getAllByUserId($user_id) {
        $sql =
            '   SELECT DISTINCT
                       p.id id,
                       p.code code
                  FROM users u
             LEFT JOIN j_users_roles ur on ur.user_id = u.id
             LEFT JOIN roles r on r.id = ur.role_id
             LEFT JOIN j_roles_permissions rp on rp.role_id = r.id
             LEFT JOIN permissions p on p.id = rp.permission_id
                 WHERE u.id = ?';
        $permissionsResult = Db::prepQuery($sql, 'i', [$user_id]);
        $permissions = [];
        foreach ($permissionsResult as $permissionResult) {
            $permission = new Permission();

            $permission->id = $permissionResult['id'];
            $permission->code = $permissionResult['code'];

            $permissions[] = $permission;
        }

        return $permissions;
    }
}

class Role {
    public $id;
    public $name;
    public $permissions;
}

class RoleDao {
    public static function getAllByUserId($user_id) {
        $sql =
            '   SELECT r.id id,
                       r.name name
                  FROM users u
             LEFT JOIN j_users_roles ur on ur.user_id = u.id
             LEFT JOIN roles r on r.id = ur.role_id
                 WHERE u.id = ?';

        $rolesResult = Db::prepQuery($sql, 'i', [$user_id]);
        $roles = [];
        foreach ($rolesResult as $roleResult) {
            $role = new Role();
            $role->id = $roleResult['id'];
            $role->name = $roleResult['name'];

            $roles[] = $role;
        }

        return $roles;
    }
}

class User {
    public $id;
    public $email;
    public $pass;
    public $roles;
    public $permissions;
    public $active;

    public function generatePassword() {
        $password = Utils::generateRandomString(12);
        $this->pass = $password;
    }

    public function hasPermission($permission) {
        if (!$this->permissions) {
            return false;
        }

        foreach ($this->permissions as $element) {
            if ($permission == $element->code) {
                return true;
            }
        }

        return false;
    }
}

class UserDao {
    public static function isNameFree($name) {
        $sql = 'SELECT count(1) as c FROM users WHERE name = ?';
        $users = Db::prepQuery($sql, 's', [$name]);
        $result = $users[0]['c'] == 0;
        return $result;
    }

    public static function isEmailFree($email) {
        $sql = 'SELECT count(1) as c FROM users WHERE email = ?';
        $users = Db::prepQuery($sql, 's', [$email]);
        $result = $users[0]['c'] == 0;
        return $result;
    }

    public static function create($user) {
        $sql = 'INSERT INTO users (name, email, pass, active) VALUES (?, ?, ?, true)';
        $password = $user->pass;
        // Encrypting Password before saving to Database
        // TODO Maybe add some salt or make it more secure
        $encryptedPass = md5($password);
        $result = Db::prepStmt($sql, 'sss', [$user->name, $user->email, $encryptedPass]);
        if ($result) {
            // TODO Send real password after test
            $result = Email::sendPassword($user->email, $password);
        } else if (Db::DEBUG_MODE) {
            echo 'Error due to insert User into the Database.';
        }

        return $result;
    }

    public static function getUserById($user_id) {
        // Loading User
        $sql = 'SELECT * FROM users WHERE id = ?';
        $usersRs = Db::prepQuery($sql, 'i', [$user_id]);
        if (count($usersRs) == 0) {
            return NULL;
        }

        $userRs = $usersRs[0];
        $user = new User();
        $user->id = $userRs['id'];
        $user->name = $userRs['name'];
        $user->email = $userRs['email'];
        $user->pass = $userRs['pass'];
        $user->active = $userRs['active'];

        // Load Roles
        $roles = RoleDao::getAllByUserId($user->id);
        $user->roles = $roles;

        // Load Permissions
        $permissions = PermissionDao::getAllByUserId($user->id);
        $user->permissions = $permissions;

        return $user;
    }

    public static function findUserByEmail($email) {
        $sql = 'SELECT * FROM users WHERE email = ?';
        $usersResult = Db::prepQuery($sql, 's', [$email]);

        if (count($usersResult) == 0) {
            return NULL;
        }

        $userResult = $usersResult[0];
        $user = UserDao::getUserById($userResult['id']);
        return $user;
    }
}

class LoginSession {
    public $id;
    public $user_id;
    public $user_ip;
    public $cookie_code;
    public $expiration;

    public function isExpired() {
        $now = DateTimeUtils::now();
        $expired = $now > $this->expiration;
        return $expired;
    }
}

class LoginDao {
    const COOKIE_NAME = 'loginSession';

    private static $currentUser;

    /**
     * Checking autologin:
     * 1. In cookies saves Session Code
     * 2. In Database stored reference between User, IP and Session Code
     * 3. Checking that:
     *    - Session Exists is Not Expired
     *    - Does User Exists and Active
     *    - the IP is the same (Cookie were not stolen)
     *    - the Session exists (by Code)
     *
     * @return NULL|User
     */
    public static function autologin() {
        if (!isset($_COOKIE[self::COOKIE_NAME])) {
            return NULL;
        }

        $cookie = $_COOKIE[self::COOKIE_NAME];
        $session = self::findSessionByCode($cookie);

        // Check Session expired
        if ($session && $session->isExpired()) {
            LoginDao::deleteSession($session->id);
            return NULL;
        }

        // Check User exists and active
        $user = UserDao::getUserById($session->user_id);
        if (!$user || !$user->active) {
            LoginDao::deleteSession($session->id);
            return NULL;
        }

        $currentUserIP = self::clientIP();
        // If IP changed (Cookie probably stolen), deleting session
        if ($session->user_ip != $currentUserIP) {
            LoginDao::deleteSession($session->id);
            return NULL;
        }

        self::$currentUser = $user;
        return $user;
    }

    /**
     * Check User (by E-Mail) and Password pair
     * Sets the User Entity to LoginDao::$currentUser
     *
     * @param string $email
     * @param string $pass
     *
     * @return string Error Text if an Error occured, otherwise NULL
     */
    public static function login($email, $pass) {
        // Loading User
        $user = UserDao::findUserByEmail($email);
        if ($user == NULL) {
            return 'Error: User with the E-Mail not found';
        }

        // Checking Locked
        if (!$user->active) {
            return 'Error: User is locked';
        }

        // Checking Password
        $stored = strtolower($user->pass);
        // TODO Change here if the Salt or something else will be added
        $actual = strtolower(md5($pass));
        if ($stored != $actual) {
            return 'Error: Password is incorrect';
        }

        self::$currentUser = $user;

        // Create Login Session
        $session = new LoginSession();
        $session->user_id = $user->id;
        $session->user_ip = self::clientIP();

        $now = DateTimeUtils::now();
        $session->cookie_code = uniqid($now->format('Ymd-His-'), true);

        $expiration = $now->add(new DateInterval('P365D')); // +365 Days
        $session->expiration = DateTimeUtils::toDatabase($expiration);
        self::insertNewSession($session);

        // Set Login Cookies
        setcookie(self::COOKIE_NAME, $session->cookie_code, time() + (86400 * 365));
    }

    public static function getCurrentUser() {
        return self::$currentUser;
    }

    private static function findSessionByCode($cookie) {
        $sql = 'SELECT * FROM login_sessions WHERE cookie_code = ?';
        $sessionResults = Db::prepQuery($sql, 's', [$cookie]);
        if (count($sessionResults) == 0) {
            return NULL;
        }

        $sessionResult = $sessionResults[0];
        $session = new LoginSession();
        $session->id = $sessionResult['id'];
        $session->user_id = $sessionResult['user_id'];
        $session->user_ip = $sessionResult['user_ip'];
        $session->cookie_code = $sessionResult['cookie_code'];
        $session->expiration = DateTimeUtils::fromDatabase($sessionResult['expiration']);

        return $session;
    }

    private static function insertNewSession($session) {
        $sql = 'INSERT INTO login_sessions (user_id, user_ip, cookie_code, expiration) VALUES (?, ?, ?, ?)';
        $parameters = [$session->user_id, $session->user_ip, $session->cookie_code, $session->expiration];
        $result = Db::prepStmt($sql, 'isss', $parameters);
        echo Db::$conn->error;
        return $result;
    }

    private static function deleteSession($session_id) {
        $sql = 'DELETE FROM login_sessions WHERE id = ?';
        $result = Db::prepStmt($sql, 'i', [$session_id]);
        return $result;
    }

    public static function clientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    private static function generateUUID() {
        $date = new DateTime();
        $prefix = $date->format('Ymd-His-');
        $uuid = uniqid($prefix, true);
        return $uuid;
    }

    public static function isLogged() {
        $logged = self::$currentUser != NULL;
        return $logged;
    }
}
?>
