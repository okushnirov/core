<?php

namespace okushnirov\core\Library;

use okushnirov\core\Library\Enums\{Auth, Decrypt, Encrypt, SessionType};

final class Authorization
{
  public static bool $debug = false;
  
  public static bool $isAdmin = false;
  
  public static bool $isAvatar = false;
  
  public static bool $isDev = false;
  
  public static bool $isLogin = false;
  
  private static ?string $userLogin;
  
  private static string $userPassword;
  
  public function __construct(?string $userLogin = null, string $userPassword = '')
  {
    self::$isLogin = !empty($_SESSION['isLogin']);
    
    if (self::$isLogin) {
      self::$isAdmin = !empty($_SESSION['isAdmin']);
      self::$isAvatar = !empty($_SESSION['isAvatar']);
      self::$isDev = !empty($_SESSION['isDev']);
      
      self::$userLogin = self::getUserLogin(is_null($userLogin) ? Crypt::action($_SESSION['CRC']['login'] ?? '',
        Decrypt::CHR) : $userLogin);
      
      self::$userPassword = '' === $userPassword ? Crypt::action(Crypt::action($_SESSION['CRC']['hash'] ?? '',
        Decrypt::CHR), Decrypt::CHR) : $userPassword;
    } else {
      self::$userLogin = self::getUserLogin($userLogin);
      self::$userPassword = $userPassword;
    }
    
    if (self::$debug) {
      trigger_error(__METHOD__." user[".self::$userLogin.':'.self::$userPassword.'] isLogin['.self::$isLogin.'] call['
        .debug_backtrace()[0]['file'].']');
    }
  }
  
  public function check(
    Auth $type = Auth::DB_USER, bool | int | null $connection = false, SessionType $session = SessionType::DB):bool
  {
    if (self::$debug) {
      trigger_error(__METHOD__." auth[$type->name] connection[$connection] session[$session->name]");
    }
    
    if (is_null(self::$userLogin)) {
      
      return false;
    }
    
    if (TEST_SERVER && $type === Auth::LDAP) {
      $type = Auth::DB;
    }
    
    self::$isAdmin = false;
    self::$isAvatar = false;
    self::$isDev = false;
    self::$isLogin = false;
    
    self::$isLogin = match ($type) {
      Auth::DB => self::testDB($connection),
      Auth::LDAP => self::testLDAP(),
      Auth::LDAP_DB => self::testLDAP() || self::testDB($connection),
      Auth::DB_USER => true
    };
    
    if (self::$debug) {
      trigger_error(__METHOD__.' isLogin['.self::$isLogin.']');
    }
    
    if (!self::$isLogin) {
      
      return false;
    }
    
    $user = $this->getUser(self::$userLogin);
    
    return $user && (SessionType::NONE === $session || $this->getUserDB($user));
  }
  
  public function addErrorCounter(string $userLogin = '', ?int $userID = null):bool
  {
    $settings = self::getSettings();
    
    if (empty($settings) || !isset($settings->login->block)) {
      trigger_error(__METHOD__." Відсутній сервіс зміни лічильника помилок входу користувача");
      
      return false;
    }
    
    $ws = $settings->login->block;
    
    $response = Curl::exec($ws->{'url'.(TEST_SERVER ? 'Test' : '')}, [
      "charset=\"utf-8\"",
      "Authorization: Basic ".base64_encode("$ws->user:$ws->pass")
    ], http_build_query([
      'userLogin' => $userLogin,
      'userID' => $userID
    ]), false, false, 1, 2, 5);
    
    return !empty($response);
  }
  
  public function getUser(string $userLogin = '', ?int $userID = null)
  {
    $settings = self::getSettings();
    
    if (empty($settings) || !isset($settings->login->auth)) {
      
      throw new \Exception($settings->login->error->{-10}->{Lang::$lang} ?? 'Empty settings', -10);
    }
    
    $ws = $settings->login->auth;
    
    $response = Curl::exec($ws->{'url'.(TEST_SERVER ? 'Test' : '')}, [
      "charset=\"utf-8\"",
      "Authorization: Basic ".base64_encode("$ws->user:$ws->pass")
    ], http_build_query(0 < $userID
      ? [
        'userID' => $userID
      ]
      : [
        'userLogin' => $userLogin
      ]), false, false, 1, 2, 5);
    
    try {
      $xml = empty($response) ? null : new \SimpleXMLElement($response);
    } catch (\Exception) {
      $xml = null;
    }
    
    if (empty($xml) || !isset($xml->result->error)) {
      $errorCode = 200 === Curl::$curlHttpCode ? -20 : -30;
      
      throw new \Exception($settings->login->error->{$errorCode}->{Lang::$lang} ?? 'Empty user info', $errorCode);
    }
    
    if ((int)$xml->result->error) {
      $errorCode = -1 === (int)$xml->result->error ? -40 : -50;
      throw new \Exception(trim(($settings->login->error->{$errorCode}->{Lang::$lang} ?? '').' #'.$xml->result->error),
        $errorCode);
    }
    
    $user = $xml->xpath('user')[0] ?? [];
    
    return $user ? : throw new \Exception($settings->login->error->{-60}->{Lang::$lang} ?? 'Empty user info', -60);
  }
  
  public function getUserDB($user):bool
  {
    $_SESSION['isAdmin'] = self::$isAdmin;
    $_SESSION['isAvatar'] = false;
    $_SESSION['isDev'] = false;
    $_SESSION['isLogin'] = self::$isLogin;
    
    $_SESSION['CRC'] = [];
    
    foreach ($user as $key => $value) {
      $value = (string)$value;
      
      if ('role' === $key) {
        $_SESSION['isAdmin'] = $_SESSION['isAdmin'] || 'рлАдминистратор' == $value;
        $_SESSION['isDev'] = $_SESSION['isDev'] || 'рлРазработчик' == $value;
      }
      
      $_SESSION['CRC'][$key] = 'avatar' === $key ? $value : Crypt::action($value, Encrypt::CHR);
    }
    
    $_SESSION['CRC']['hash'] = Crypt::action(Crypt::action(self::$userPassword, Encrypt::CHR), Encrypt::CHR);
    
    return self::updateSession();
  }
  
  public function updateSession():bool
  {
    if (!self::$isLogin) {
      if (session_id()) {
        unset($_SESSION['CRC'], $_SESSION['isLogin'], $_SESSION['isAdmin'], $_SESSION['isDev']);
      }
      
      return false;
    }
    
    self::$isAdmin = !empty($_SESSION['isAdmin']);
    self::$isDev = !empty($_SESSION['isDev']);
    
    if (self::$debug) {
      trigger_error(__METHOD__.' user['.self::$userLogin.'] isAdmin['.self::$isAdmin.'] isDev['.self::$isDev.']');
    }
    
    return true;
  }
  
  private function getSettings(array $files = ['/json/login.json']):mixed
  {
    
    return File::parse($files);
  }
  
  private function getUserLogin(?string $userLogin):?string
  {
    
    return is_null($userLogin) || '' === trim($userLogin) ? null : $userLogin;
  }
  
  private function testDB(int | bool $connection):bool
  {
    $settings = self::getSettings(['/json/dbase.json']);
    
    if (empty($settings)) {
      
      throw new \Exception('Empty login settings', -100);
    }
    
    if (false === $connection) {
      $connection = (int)$settings->dbase->{'login'.(TEST_SERVER ? 'Test' : '')};
    }
    
    if (false !== $connection && !isset($settings->dbase->{"$connection"})) {
      
      throw new \Exception('Wrong DB connection settings', -105);
    }
    
    DbSQLAnywhere::disconnect();
    
    if (!DbSQLAnywhere::connect($connection)) {
      
      throw new \Exception('Wrong DB connect', -110);
    }
    
    DbSQLAnywhere::disconnect();
    
    if (!DbSQLAnywhere::connect($connection, self::$userLogin, self::$userPassword)) {
      
      throw new \Exception('Wrong DB connect', -115);
    }
    
    DbSQLAnywhere::disconnect();
    
    return true;
  }
  
  private function testLDAP():bool
  {
    $settings = self::getSettings();
    
    if (empty($settings->login) || !isset($settings->login->ldap)) {
      
      throw new \Exception('Empty login settings', -200);
    }
    
    $ldap = $settings->login->ldap->{0};
    
    if (self::$debug) {
      trigger_error(__METHOD__." Settings\n".json_encode($ldap, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    if (empty($ldap->domain)
      || empty($ldap->host)
      || empty($ldap->port)
      || empty($ldap->member)
      || empty($ldap->base)) {
      
      throw new \Exception('Wrong LDAP parameters', -205);
    }
    
    $login = mb_eregi_replace($ldap->domain, '', self::$userLogin).$ldap->domain;
    
    ldap_set_option(null, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option(null, LDAP_OPT_NETWORK_TIMEOUT, 3);
    ldap_set_option(null, LDAP_OPT_TIMELIMIT, 3);
    
    $connect = ldap_connect($ldap->host, $ldap->port);
    
    if (!$connect) {
      if (self::$debug) {
        trigger_error(__METHOD__." Connect to $ldap->host:$ldap->port is failed");
      }
      
      return false;
    }
    
    $bind = ldap_bind($connect, $login, self::$userPassword);
    
    if (!$bind) {
      if (self::$debug) {
        trigger_error(__METHOD__." Bind [$login:".self::$userPassword.'] is failed');
      }
      
      return false;
    }
    
    $cnt = 0;
    
    foreach ($ldap->member as $key => $value) {
      $filter = "(&(memberOf=$value)(sAMAccountName=".self::$userLogin."))";
      $resultSearchLDAP = ldap_search($connect, $ldap->base, $filter);
      
      if (self::$debug) {
        trigger_error(__METHOD__." Base $ldap->base\n Filter $filter");
      }
      
      if (false === $resultSearchLDAP) {
        
        continue;
      }
      
      $resultEnter = ldap_get_entries($connect, $resultSearchLDAP);
      $cnt = (int)($resultEnter['count'] ?? 0);
      
      if (1 === $cnt && 'admin' === $key) {
        self::$isAdmin = true;
        
        break;
      }
    }
    
    self::$isLogin = 1 === $cnt;
    
    if (self::$debug) {
      trigger_error(__METHOD__.' Result ['.self::$isLogin.'] isAdmin ['.self::$isAdmin.']');
    }
    
    return self::$isLogin;
  }
}