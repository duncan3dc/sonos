<?php

namespace Sonos;

class Controller extends Speaker {


    public function __construct(Speaker $speaker) {

        $this->ip = $speaker->ip;

        $this->name = $speaker->name;
        $this->room = $speaker->room;
        $this->group = $speaker->getGroup();

    }


    protected function soap($service,$action,$params=[]) {

        switch($service) {
            case "AVTransport";
            case "RenderingControl":
                $path = "MediaRenderer";
            break;
            case "ContentDirectory":
                $path = "MediaServer";
            break;
            default:
                throw new \Exception("Unknown service (" . $service . ")");
            break;
        }

        $soap = new \SoapClient(null,[
            "location"  =>  "http://" . $this->ip . ":1400/" . $path . "/" . $service . "/Control",
            "uri"       =>  "urn:schemas-upnp-org:service:" . $service . ":1",
        ]);

        $soapParams = [];
        $params["InstanceID"] = 0;
        foreach($params as $key => $val) {
            $soapParams[] = new \SoapParam(new \SoapVar($val,XSD_STRING),$key);
        }

        return $soap->__soapCall($action,$soapParams);
    }


    public function isCoordinator() {
        return true;
    }


    public function getState() {
        $data = $this->soap("AVTransport","GetTransportInfo");
        return $data["CurrentTransportState"];
    }


    public function play() {
        return $this->soap("AVTransport","Play",[
            "Speed"         =>  1,
        ]);
    }


    public function pause() {
        return $this->soap("AVTransport","Pause");
    }


    public function next() {
        return $this->soap("AVTransport","Next");
    }


    public function previous() {
        return $this->soap("AVTransport","Previous");
    }


}
