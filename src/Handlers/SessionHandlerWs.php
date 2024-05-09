<?php

namespace okushnirov\core\Handlers;

use okushnirov\core\Library\{Enums\Charset, File, WebService};

final class SessionHandlerWs implements \SessionHandlerInterface
{
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
    return 0 === (int)(self::ws('open')['error'] ?? -1);
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
    $request = new \DOMDocument();
    $root = $request->appendChild($request->createElement('request'));
    $root->setAttribute('method', $method);
    $root->setAttribute('ip', trim($_SERVER['REMOTE_ADDR'] ?? ''));
    
    foreach ($data as $key => $value) {
      $root->appendChild($request->createElement($key, htmlspecialchars($value, ENT_XML1)));
    }
    
    $request->formatOutput = true;
    $session = File::parse(['/json/session.json']);
    
    if (empty($session)) {
      throw new \Exception('Empty session settings');
    }
    
    try {
      $response = (new WebService($session))->xml('session', $request->saveXML(), timeout: 2, charset: Charset::WINDOWS1251);
      unset($request);
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }
    
    return $response;
  }
}