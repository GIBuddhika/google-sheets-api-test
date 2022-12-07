<?php

namespace App\Handler;

use Google_Service_Sheets;

class ServersHandler
{
    public function readGoogleSheet()
    {
        $client = new \Google\Client();

        $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
        // $client->setAuthConfig(__DIR__ . '/credentials.json');
        $client->setAuthConfig(realpath(__DIR__ . '/../../credentials.json'));
        $service = new Google_Service_Sheets($client);
        $spreadsheetId = $_ENV['SPREADSHEET_ID']; //It is present in your URL
        $get_range = $_ENV['SPREADSHEET_RANGE'];
        $response = $service->spreadsheets_values->get($spreadsheetId, $get_range);
        $serverData = $response->getValues();
        return $serverData;
    }

    public function getLocations($serverData)
    {
        foreach ($serverData as $id => $item) {
            $locations[] = $item[3];
        }

        return array_values(array_unique($locations));
    }

    public function filterServers($filters, $serverData)
    {
        //filter by location
        $filteredByLocation =  $this->filterByLocation($filters, $serverData);

        //filter by disk type
        $filteredByLocationAndDiskType =  $this->filterByDiskType($filters, $filteredByLocation);

        // filter by ram size
        $filteredByLocationAndDiskTypeAndRam =  $this->filterByRam($filters, $filteredByLocationAndDiskType);

        // filter by price storage
        $filteredByAll = $this->filterByStorage($filters, $filteredByLocationAndDiskTypeAndRam);

        return $filteredByAll;
    }

    private function filterByLocation($filters, $serverData)
    {
        if (isset($filters['location'])) {
            $filteredLocationsKeys = array_keys(array_column($serverData, 3), $filters['location']);
            $filteredLocations = [];
            foreach ($filteredLocationsKeys as $key) {
                $filteredLocations[] = $serverData[$key];
            }
            return $filteredLocations;
        }
        return $serverData;
    }

    private function filterByDiskType($filters, $serverData)
    {
        if (isset($filters['disk_type'])) {
            $filteredDiskTypeKeys = array_keys(preg_grep("/" . $filters['disk_type'] . "/i", array_column($serverData, 2)));
            $filteredDisksTypes = [];
            foreach ($filteredDiskTypeKeys as $key) {
                $filteredDisksTypes[] = $serverData[$key];
            }
            return $filteredDisksTypes;
        }
        return $serverData;
    }

    private function filterByRam($filters, $serverData)
    {
        if (isset($filters['ram'])) {
            $ramsList = json_decode($filters['ram']);
            $filteredRamKeys = [];
            foreach ($ramsList as $key => $ram) {
                $filteredRamKeys = array_merge($filteredRamKeys, array_keys(preg_grep("/^" . $ram . "/i", array_column($serverData, 1))));
            }
            $filteredRams = [];
            foreach ($filteredRamKeys as $key) {
                $filteredRams[] = $serverData[$key];
            }
            return $filteredRams;
        }
        return $serverData;
    }

    private function filterByStorage($filters, $serverData)
    {
        if (isset($filters['storage_min']) && isset($filters['storage_max'])) {
            $covertToTB = function (array $value): array {
                if (strpos($value[2], "GB")) {
                    $diskSizeArray =  explode("x", substr($value[2], 0, strpos($value[2], "GB")));
                    $value[5] = ($diskSizeArray[0] * $diskSizeArray[1]) / 1000;
                } else {
                    $diskSizeArray = explode("x", substr($value[2], 0, strpos($value[2], "TB")));
                    $value[5] = $diskSizeArray[0] * $diskSizeArray[1];
                }
                return $value;
            };

            $storageInTB = array_map($covertToTB, $serverData);

            $gtMinValue = array_keys(array_filter($storageInTB, function ($server) use ($filters) {
                return $server[5] >= $filters['storage_min'];
            }));

            $ltMaxValue = array_keys(array_filter($storageInTB, function ($server) use ($filters) {
                return $server[5] <= $filters['storage_max'];
            }));

            $filteredRange = [];
            foreach (array_intersect($gtMinValue, $ltMaxValue) as $key) {
                $filteredRange[] = $storageInTB[$key];
            }
            return $filteredRange;
        }
        return $serverData;
    }
}
