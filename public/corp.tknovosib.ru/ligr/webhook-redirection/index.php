<?
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

class Redirect
{
    const LOG_PATH = __DIR__ . '/logs/';
    const LOCK_PATH = __DIR__ . '/lock/';
    const UNLOCK_DELAY = 5;

    private $entity;
    private $lockFile;
    private $request;
    private $uniqId;

    public function __construct()
    {
        $this->request = $_REQUEST;
        $this->uniqId = uniqid();

        $checkRequest = $this->checkRequest();

        if (!$checkRequest) {
            $error = [
                'message' => 'Вебхук не прошел проверку',
                'request' => $this->request
            ];
            $this->log($error, 'error.txt');
            return false;
        }

        if (!empty($this->request['url'])) {
            $this->call();
        }

        $this->unlock();
    }

    private function lock()
    {
        if (!is_dir(self::LOCK_PATH)) {
            mkdir(self::LOCK_PATH, 0777, true);
        }
        $this->lockFile = fopen(self::LOCK_PATH . $this->entity . '.lock', 'x');
    }

    private function unlock()
    {
        if ($this->lockFile === false) return;

        sleep(self::UNLOCK_DELAY);
        fclose($this->lockFile);
        unlink(self::LOCK_PATH . $this->entity . '.lock');
    }

    private function checkRequest()
    {
        if (empty($this->request['event'])) return false;
        if (empty($this->request['data']['FIELDS']['ID'])) return false;

        $this->entity = $this->request['event'] . '_' . $this->request['data']['FIELDS']['ID'] . '_' . $this->request['auth']['application_token'];
        $this->lock();

        if ($this->lockFile === false) {
            $this->entityLog('Сущность заблокирована', 'lock');
            return false;
        }
        return true;
    }

    private function log($array, $filename = 'log.txt')
    {
        $log = date('Y-m-d H:i:s') . ' ' . print_r($array, true);
        file_put_contents(__DIR__ . '/' . $filename, $log . PHP_EOL, FILE_APPEND);
    }

    private function entityLog($data, $filename = 'log.txt')
    {
        return;
        $dir = self::LOG_PATH . $this->entity;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $log = date('Y-m-d H:i:s') . ' ' . print_r($data, true);
        file_put_contents($dir . '/' . $filename . $this->uniqId . '_' . date('d.m.Y H:i:s') . '.txt', $log . PHP_EOL, FILE_APPEND);
    }

    private function call()
    {
        $ch = curl_init();

        $url = $this->request['url'];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->request));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        $success = [
            'message' => $result,
            'request' => $this->request
        ];
        $this->log($success, 'success.txt');
        $this->entityLog($success, 'success');
    }
}

new Redirect();