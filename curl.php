<?php

class curl_request
{
    private $dbUsername;
    private $dbPassword;
    private $dbServer;
    private $dbName;

    private $databaseConnection;

    public function getResponseFromGiphy($query, $limit = 1, $offset = 1) {
        $data = $this->_getApiResponseViaCurl(urlencode($query), $limit, $offset);
        return $this->_checkDatabaseForData($this->_processApiResponse($data));
    }

    private function _getApiKey() {
        return ''; //put api key here
    }

    private function _getApiResponseViaCurl($query, $limit, $offset) {
        //$url = 'http://api.giphy.com/v1/gifs/search?q='.$query.'&limit='.$limit.'&offset='.$offset.'&api_key='.$this->_getApiKey();
        $url = 'http://api.giphy.com/v1/gifs/random?tag='.$query.'&api_key='.$this->_getApiKey();
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 4);
        $json = curl_exec($ch);
        if(!$json) {
            echo curl_error($ch);
        }
        curl_close($ch);
        return $json;
    }

    private function _processApiResponse($data)
    {
        $decoded = json_decode($data);
        $toReturn = array('id'=>$decoded->data->id, 'image'=>$decoded->data->image_original_url);
        return $toReturn;
    }

    private function _checkDatabaseForData($data)
    {
        $this->_setDatabaseConnectionDetails();
        if($this->runQueryOnDatabase($data['id']) <= 0) {
            $this->postImageToDatabase($data);
            return $data;
        }
        return 0;
    }

    private function _setDatabaseConnectionDetails()
    {
        $this->dbServer = 'localhost';
        $this->dbName = '';
        $this->dbUsername = '';
        $this->dbPassword = '';
    }

    private function _connectToDatabase()
    {
        $this->databaseConnection = new PDO('mysql:host='.$this->dbServer.';dbname='.$this->dbName, $this->dbUsername, $this->dbPassword);
    }

    private function _disconnectDatabase()
    {
        $this->databaseConnection = null;
    }

    private function runQueryOnDatabase($id)
    {
        $this->_connectToDatabase();
        $query = $this->databaseConnection->prepare('SELECT * FROM gifs WHERE img_id='.$id);
        $query->execute();
        $this->_disconnectDatabase();
        return $query->rowCount();
    }

    private function postImageToDatabase($data)
    {
        $imgId = $data['id'];
        $this->_connectToDatabase();
        $query = $this->databaseConnection->prepare('INSERT INTO gifs (`img_id`, `count`) VALUES (\''.$imgId.'\', 1)');
        $query->execute();
        $this->_disconnectDatabase();
        return $query->rowCount();
    }
}