<?php

namespace okushnirov\core\Handlers;

use okushnirov\core\Library\{Config, Curl, Enums\Charset};

final class SessionHandlerWs implements \SessionHandlerInterface
{
  private static ?array $config = null;
  
  public function __construct()
  {
    session_set_save_handler([
      $this,
      'open'
    ], [
      $this,
      'close'
    ], [
      $this,
      'read'
    ], [
      $this,
      'write'
    ], [
      $this,
      'destroy'
    ], [
      $this,
      'gc'
    ]);
  }
  
  public function close():bool
  {
    return true;
  }
  
  public function destroy(string $id):bool
  {
    return 0 === (int)(self::ws('destroy', ['id' => $id])['error'] ?? -1);
  }
  
  public function gc(int $max_lifetime):int | false
  {
    $response = self::ws('gc', ['max_lifetime' => $max_lifetime]);
    
    return 0 === (int)($response['error'] ?? -1) ? (int)($response->count ?? 0) : false;
  }
  
  public function open(string $path, string $name):bool
  {
    return true;
  }
  
  public function read(string $id):string | false
  {
    $response = self::ws('read', ['id' => $id]);
    
    return 0 === (int)($response['error'] ?? -1) ? trim($response->data ?? '') : false;
  }
  
  public function write(string $id, string $data):bool
  {
    return 0 === (int)(self::ws('write', [
        'id' => $id,
        'data' => $data
      ])['error'] ?? -1);
  }
  
  private function ws(string $method, array $data = []):?\SimpleXMLElement
  {
    Config::load(['session.php']);
    
    self::$config ??= Config::get('session') ?? null;
    
    if (empty(self::$config)) {
      
      throw new \Exception('Empty session settings');
    }
    
    $request = new \DOMDocument('1.0', Charset::UTF8->value);
    $root = $request->appendChild($request->createElement('request'));
    $root->setAttribute('method', $method);
    $root->setAttribute('ip', trim($_SERVER['REMOTE_ADDR'] ?? ''));
    
    foreach ($data as $key => $value) {
      $root->appendChild($request->createElement($key, htmlspecialchars($value, ENT_XML1)));
    }
    
    $request->formatOutput = true;
    
    $response = Curl::exec(self::$config['url'.(TEST_SERVER ? 'Test' : '')] ?? self::$config['url'] ?? '', [
      'Content-Type: application/xml;charset=utf-8'
    ], $request->saveXML(), timeout: 2);
    
    try {
      $xml = $response && 200 === Curl::$curlHttpCode ? new \SimpleXMLElement($response) : null;
    } catch (\Exception $e) {
      trigger_error(__METHOD__.' Exception '.$e->getMessage()." [{$e->getCode()}]");
      $xml = null;
    }
    
    return $xml;
  }
}