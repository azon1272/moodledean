<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * otapilib.php - OpenTechnology services API library
 *
 * @package    mdlotdistr
 * @subpackage lib
 * @copyright  2013 Alex Djachenko, Kirill Krasnoschekov, Ilya Fastenko
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Класс для стандартных операций по получению серийника
 *
 * Требуются следующие языковые строки:
 * (см. lang-файл плагина admin_tool_otserial)
 *
 * @author krasnoschekov
 *
 */


/**
 * Класс доступа к OT API
 *
 * @author krasnoschekov
 *
 */
abstract class block_dof_otserial_base
{
    public static $version = 2013122800;

    private $clientsurl = 'https://clients.opentechnology.ru/';
    private $requesturl = 'https://api.opentechnology.ru/';
    private $otserialuri = 'otserial/index.php';

    /** @var string Код продукта */
    protected $pcode;
    /** @var string Версия продукта */
    protected $pversion;
    /** @var string URL продукта */
    protected $purl;

    // Идентификационные параметры ОТ
    protected $otserial = '';
    protected $otkey = '';

    // Настройки плагина
    // должны быть установлены в классе-наследнике
    public $code_cfg;
    public $code_str;
    public $code_param;
    public $tariffcodes;

    /**
     * Абстрактный метод, задающий параметры плагина
     */
    protected abstract function setup_plugin_cfg();

    /**
     * Конструктор: задаёт код продукта, версию, url
     * @param string $pcode Код продукта
     * @param string $pversion Версия продукта
     * @param string $purl URL
     * @param array $params Массив дополнительных параметров
     */
    public function __construct($pcode, $pversion, $purl, $params=array())
    {
        // Параметры плагина
        $this->pcode = $pcode;
        $this->pversion = $pversion;
        $this->purl = $purl;

        // Параметры системы
        global $CFG;
        if (!empty($params['upgrade']))
        {// Запрос во время обновления
            // Обновление: в конфиге версия ещё не обновилась, и чтобы
            // сообщить серверу новую версию, её приходится читать прямо
            // из файла
            require($CFG->dirroot . '/version.php');
            $this->mversion = $version;
            $this->mrelease = $release;
        }
        else
        {// Регулярный запрос: версию можно спросить у системы
            $this->mversion = $CFG->version;
            $this->mrelease = $CFG->release;
        }

        $this->setup_plugin_cfg();
    }

    /**
     * Проверить статус
     * Если серийника не было -- получить его.
     * @return array('status' => true/false, 'messages' => array, 'response' => array)
     */
    public function check_or_get_serial()
    {
        // Возвращаемое значение
        $result = array('status' => false, 'messages' => array());

        $otserial = get_config($this->code_cfg, 'otserial');
        $otkey = get_config($this->code_cfg, 'otkey');

        // В конфиге не нашлось: пытаемся получить
        if (empty($otserial) OR empty($otkey)) {
            $result['response'] = $otdata = $this->get_otserial();
            if (isset($otdata->status) AND preg_match('/^error/', $otdata->status)) {
                // Сервер не выдал серийник, вернул ошибку
                $msg = $otdata->message;
                $result['messages'][] = get_string('get_otserial_fail', $this->code_str, $msg);
            } elseif (!empty($otdata->otserial) AND !empty($otdata->otkey)) {
                // Сервер вернул серийник и ключ, сохраняем в конфиг
                set_config('otserial', $otdata->otserial, $this->code_cfg);
                set_config('otkey', $otdata->otkey, $this->code_cfg);
                $otserial = $otdata->otserial;
                $otkey = $otdata->otkey;
            }
        }

        // Проверяем статус
        if (!empty($otserial) AND !empty($otkey)) {
            $result['response'] = $stdata = $this->get_otserial_status($otserial, $otkey);
            if (isset($stdata->status) AND preg_match('/^error/', $stdata->status)) {
                // Ошибка проверки серийника, показываем пользователю
                $msg = $stdata->message;
                $result['messages'][] = get_string('otserial_check_fail', $this->code_str, $msg);
            } else {
                // Серийник прошел проверку
                $result['status'] = true;
                $result['messages'][] = get_string('otserial_check_ok', $this->code_str);
            }
        }

        return $result;
    }

    /**
     * Получить серийник
     *
     * @return object stdClass
     * ->otserial
     * ->otkey
     *
     * @author Ilya Fastenko 2013
     */
    public function get_otserial()
    {
        //время отправки запроса
        $time = 10000*microtime(true);

        //url запроса
        $url = $this->requesturl . $this->otserialuri;
        //параметры запроса
        $params = array(
                'do' => 'get_serial',
                'time' => $time,
        );

        ////////////////////////////////////////
        // Данные для передачи

        // Базовое приложение
        if ($bdata = $this->get_bproduct_data()) {
            //серийник базового приложения
            $bpotserial = $bdata->otserial;
        } else {
            $bpotserial = '';
        }

        //от этих данных берётся хэш
        $data = array(
                'pcode' => $this->pcode,
                'pversion' => $this->pversion,
                'purl' => $this->purl,
                'bpotserial' => $bpotserial,

                'mversion' => $this->mversion,
                'mrelease' => $this->mrelease,
        );

        if (!empty($bdata->otkey))
        {
            // если есть базовое приложение, пользуемся его ключом, чтобы
            // подтвердить аутентичность
            $params['hash'] = $this->calculate_hash($bdata->otkey, $time, $data);
        }

        //отправляем запрос на получение серийника
        try {
            $response = json_decode($this->request($url, $params+$data));
        } catch (Exception $e) {
            $response = new stdClass();
            $response->status = "error_connection";
            $response->message = "Не удалось получить ключ продукта.";
        }
        return $response;
    }

    /**
     * Проверить статус продукта
     * Возвращает полученный ответ
     * @param object $otdata stdClass
     * ->otserial
     * ->otkey
     * @return string $response - статус серийника
     *
     * @author Ilya Fastenko 2013
     */
    public function get_otserial_status($otserial, $otkey)
    {
        //время отправки запроса
        $time = 10000*microtime(true);

        //url запроса
        $url = $this->requesturl . $this->otserialuri;
        //параметры запроса
        $params = array(
                'do'=>'get_status',
                'time' => $time,
        );

        ////////////////////////////////////////
        // Данные для передачи

        // Серийник и секретный ключ
        $this->otserial = $otserial;
        $this->otkey = $otkey;

        // Базовое приложение
        if ($bdata = $this->get_bproduct_data()) {
            //серийник базового приложения
            $bpotserial = $bdata->otserial;
        } else {
            $bpotserial = '';
        }

        //данные для передачи (от них берётся хэш)
        $data = array(
                'pcode' => $this->pcode,
                'pversion' => $this->pversion,
                'purl' => $this->purl,
                'otserial' => $otserial,
                'bpotserial' => $bpotserial,

                'mversion' => $this->mversion,
                'mrelease' => $this->mrelease,
        );

        $params['hash'] = $this->calculate_hash($otkey, $time, $data);

        //отправляем запрос на получение серийника
        try {
            $response = json_decode($this->request($url, $params+$data));
        } catch (Exception $e) {
            $response = new stdClass();
            $response->status = "error_connection";
            $response->message = "Не удалось получить ключ продукта.";
        }
        return $response;
    }

    /**
     * Получить информацию о базовом продукте (moodle otserial)
     */
    protected function get_bproduct_data()
    {
        $data = new stdClass();
        $data->otserial = get_config('core', 'otserial');
        $data->otkey = get_config('core', 'otkey');

        if (!empty($data->otserial) AND !empty($data->otkey)) {
            return $data;
        }

        return false;
    }

    /**
     * Сформировать ссылку и добавить к ней хеш из key, time, otserial
     * @param unknown_type $str
     * @param array $params
     * @return moodle_url
     */
    public function url($str, array $params = array(), $prefix='clients')
    {
        $params['time'] = 10000*microtime(true);
        $params['otserial'] = $this->otserial;
        $params['hash'] = $this->calculate_hash($this->otkey, $params['time'], array($this->otserial));
        switch ($prefix)
        {
            case 'clients':
                $baseurl = $this->clientsurl;
                break;
            case 'api':
            default:
                $baseurl = $this->requesturl;
        }
        return new moodle_url($baseurl.$str, $params);
    }

    /**
     * Считает хеш от параметров запроса, ключа продукта OT и метки времени
     * @param string $otkey Ключ продукта ОТ
     * @param int $counter Метка времени
     * @param array $data Параметры запроса
     */
    private function calculate_hash($otkey, $counter, array $data)
    {
        return sha1("{$otkey}{$counter}" . implode('', $data));
    }
    /**
     * Выполнить запрос по указанному url с указанными параметрами
     *
     * @param string $url
     * @param array $get
     * @param array $post
     */
    private function request($url, array $get = array(), array $post=array())
    {
        // GET-параметры
        if (!empty($get))
        {
            $url .= "?";
            foreach ($get as $key => $value)
            {
                $url .= "{$key}=" . urlencode($value) . "&";
            }
        }

        $ch = curl_init($url);

        // Опции cURL
        $options = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSLVERSION => 1,
        );

        // POST-параметры
        if (!empty($post))
        {
            $options['CURLOPT_POST'] = 1;
            $options['CURLOPT_POSTFIELDS'] = http_build_query($post);
        }
        curl_setopt_array($ch, $options);

        // Выполняем запрос и получаем результат
        if ( !($rawret = curl_exec($ch)) )
        {// Ошибка
            $error = (string) curl_errno($ch);
            $error .= curl_error($ch);
            throw new Exception($error);
            return false;
        }
        // Завершаем соединеие
        curl_close($ch);

        return $rawret;
    }
}



/**
 * Класс, реализующий взаимодействие с apiot
 * @author Kirill Krasnoschekov, Ilya Fastenko 2013
 */
class block_dof_otserial extends block_dof_otserial_base
{
    protected function setup_plugin_cfg()
    {
        $this->code_cfg = 'block_dof';
        $this->code_str = 'block_dof';
        $this->code_param = 'dof_';
        $this->tariffcodes = array(
                'free',
                'Д-1', 'Д-2', 'Д-3', 'Д-Люкс',
                'Р',
                'П-Ла', 'П-Ко', 'П-Ве', 'П-Ун',
        );
    }

    public function __construct($upgrade=false)
    {
        global $CFG;
        $plugin = new stdClass();
        require($CFG->dirroot . '/blocks/dof/version.php');
        //URL приложения
        $purl = $CFG->wwwroot;

        parent::__construct($plugin->component, $plugin->version, $purl,
                array('upgrade'=>$upgrade));
    }
}

?>
