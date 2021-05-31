<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use stdClass;

class RedirectController extends Controller
{
    public function listAllRedirects(): View
    {
        $response = $this->universalApiClient('', 'GET', '');
        $parsed = json_decode(strval($response));
        $array = $parsed->items;
        return view('list', ["response" => $array]);
    }

    public function addNewRecord(Request $request):View
    {
        $req = $request->all();
        unset($req['_token']);

        if (isset($req['ttl'])) {
            $req['ttl'] = intval($req['ttl']);
        }
        if (isset($req['prio'])) {
            $req['prio'] = intval($req['prio']);
        }
        if (isset($req['port'])) {
            $req['port'] = intval($req['port']);
        }
        if (isset($req['weight'])) {
            $req['weight'] = intval($req['weight']);
        }

        $apiReq = $this->universalApiClient('', 'POST', json_encode($req));
        $apiReq = json_decode($apiReq);
        return \view('added', ["response" => $apiReq]);
    }

    public function showFieldsOfRecord(Request $request): string
    {
        $type = $request->type;
        return $this->getValuesForRecord($type);
    }

    public function deleteRecord($id): View
    {
        $res = $this->universalApiClient('/' . $id, 'DELETE', '');
        $res = json_decode($res);
        return view('deleted', ["response" => $res]);
    }

    private function getValuesForRecord($type): string
    {
        $fields = new stdClass;
        switch ($type) {
            case 'A':
                $fields->name = "subdomain name or @ if you don't want subdomain";
                $fields->content = "IPv4 address in dotted decimal format, i.e. 1.2.3.4";
                return json_encode($fields);
            case 'AAAA':
                $fields->name = "subdomain name or @ if you don't want subdomain";
                $fields->content = "IPv6 address ex. 2001:db8::3";
                return json_encode($fields);
            case 'ANAME':
                $fields->name = "value: @ or empty string";
                $fields->content = "the canonical hostname something.scaledo.com";
                return json_encode($fields);
            case 'CNAME':
                $fields->name = "subdomain name";
                $fields->content = "the canonical hostname something.scaledo.com";
                return json_encode($fields);
            case 'MX':
                $fields->name = "subdomain name or @ if you don't want subdomain";
                $fields->content = "domain name of mail servers, i.e. mail1.scaledo.com";
                $fields->prio = "record priority";
                return json_encode($fields);
            case 'NS':
                $fields->name = "subdomain name or @ if you don't want subdomain";
                $fields->content = "the canonical hostname of the DNS server, i.e. cfns1.scaledo.com";
                return json_encode($fields);
            case 'SRV':
                $fields->name = "subdomain name or @ if you don't want subdomain";
                $fields->content = "the canonical hostname of the machine providing the service";
                $fields->prio = "record priority";
                $fields->port = "the TCP or UDP port on which the service is to be found";
                $fields->weight = "a relative weight for records with the same priority";
                return json_encode($fields);
            case 'TXT':
                $fields->name = "subdomain name or @ if you don't want subdomain";
                $fields->content = "text used for DKIM or other purposes";
                return json_encode($fields);
            default:
                return 'Invalid request.';
        }
    }

    /*
     * Driver function
     */
    private function universalApiClient(string $query, string $method, string $data): string
    {
        $time = time();
        $path = '/v1/user/self/zone/php-assignment-2.ws/record' . $query;
        $api = 'https://rest.websupport.sk';
        $apiKey = getenv('API_KEY');
        $secret = getenv('API_SECRET');
        $canonicalRequest = sprintf('%s %s %s', $method, $path, $time);
        $signature = hash_hmac('sha1', $canonicalRequest, $secret);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('%s%s', $api, $path));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $apiKey . ':' . $signature);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Date: ' . gmdate('Ymd\THis\Z', $time),
            'Content-Type:application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

}


