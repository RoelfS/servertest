<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $strBearerToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIyMyIsImp0aSI6IjNmZmY2N2FiODdiMTQ5YjVkY2M1Y2E5OWYyZmIwYWRmZTA4OGVjNTg0MDM0MWFhNjU5YjZmNjM1NDIxODhhZGNiN2YzOGY4Yjk3NjA4ODg4IiwiaWF0IjoxNjgyMzM3NzM4LjU3MzM5MiwibmJmIjoxNjgyMzM3NzM4LjU3MzQwMywiZXhwIjo0ODM4MDExMzM4LjU2MTY1MSwic3ViIjoiNyIsInNjb3BlcyI6W119.m6JKmBopk-Hyw-jDGBltMd4pnASGaqGPdMReTkBn5tmsMWUUPK7eTZ02qw3LgASw6UyIJBrWIhNWF1APq2HxRm_RHSrcCqLvsUPfzLXtbHruiI5Wv47_0fzx6Pzc9O9AKt6hV3gX1-z5Sh-To-uvT3vC57QbNm3UxfIdAeijpN1WbbaYevj311yZa0pj5q3zDdK8WVSefxxl_CF8YeCWQ742tH98k6fSwci_wRcbf_SnLdrzb9sA9V2oitP29_NrbaBwpNdpa_D4H8UWeyNbynxxQslWYDUo13AqU1uS1oCel2oaaetPyMI-72aDQV6bVuQAx4KAOP296fEYseuTRYVfGirsl_eEjZXQYPCat5veO3691M9SvfOQ8CvS4Te9YYO11Fvzn6dawmCeyE45U-FOEUIxWiAl43NA1vv79Rap56uWHLQZN93R-D14QacxouC1D3YjOYsvXhdX3QxCJBVvWHy6PldoX7x8Y8WoOgVZFmbm9k00bloSZWJW0lYnwwYbo5BnWLOIuojwEILzSPVcf5JKngJmWIaoYRdmQmEy5qiudKLT-qiObYjwPhmIE1-R2AChBwmrzQpaPnnD8KY8WduKGe-7U2sDYiog426yCswsFRO3-_wIzdIf0Xrg6--7QczGvKnZqz7cP_h24mP_oqZxYYvVCvPovCqz770';
        $strAuthToken = "Authorization: Bearer " . $strBearerToken;
        
        //Get a list of all the servers on the system
        $strUrl = 'https://solus.ictglobe.co.za:443/api/v1/servers';
                
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $strAuthToken ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_URL, $strUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        if (!$result)
        {
            die("Connection Failure");
        }
        curl_close($ch);
        $objResultData = json_decode($result);
        
        //Loop through and get all the server IDs (the current list does not have the project ID
        $arrServerIds = [];
        foreach ($objResultData->data as $key => $value)
        {
            $arrServerIds[] += $value->id;
        }
        
        //Call each server individually and update locally if required
        foreach ($arrServerIds as $key => $value)
        {
            $strUrl = 'https://solus.ictglobe.co.za:443/api/v1/servers/' . $value;
                
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $strAuthToken ));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_URL, $strUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $result = curl_exec($ch);
            if (!$result)
            {
                die("Connection Failure");
            }
            curl_close($ch);
            $objResultData = json_decode($result);
            
            //We only want to servers with project ID 8
            
            if ($objResultData->data->project->id == 8)
            {
                $intServerId = $objResultData->data->id;
                $arrServerList[$intServerId]['id'] = $intServerId;
                $arrServerList[$intServerId]['name'] = $objResultData->data->name;
                $arrServerList[$intServerId]['description'] = $objResultData->data->description;
                $arrServerList[$intServerId]['project'] = 8;
                $arrServerList[$intServerId]['disk'] = $objResultData->data->specifications->disk;
                $arrServerList[$intServerId]['ram'] = $objResultData->data->specifications->ram;
                $arrServerList[$intServerId]['vcpu'] = $objResultData->data->specifications->vcpu;
                $arrServerList[$intServerId]['plan_id'] = $objResultData->data->plan->id;
                $arrServerList[$intServerId]['plan_name'] = $objResultData->data->plan->name;
                $arrServerList[$intServerId]['status'] = $objResultData->data->status;
                $arrServerList[$intServerId]['ips'] = $objResultData->data->ips[0]->ip;
                $arrServerList[$intServerId]['location'] = $objResultData->data->location->id;
                $arrServerList[$intServerId]['os'] = 16;
            }
        }        
        
        
        //Compare all the ID received from the API with the IDs in the DB to determine which to update and which to create
        $arrDbServers = Server::all();
        
        $arrDbServerIds = [];
        $arrApiServerIds = [];
        foreach ($arrDbServers as $arrDbServer)
        {
            $arrDbServerIds[] += $arrDbServer['id'];
        }
         
        foreach ($arrServerList as $arrServer)
        {
            $arrApiServerIds[] += $arrServer['id'];
        }
        
        $arrNewServerIds = array_diff($arrApiServerIds, $arrDbServerIds);
        $this->updateServersFromApi($arrServerList, $arrDbServers, $arrNewServerIds);
        $servers = Server::latest()->paginate(5);
        return view('servers.index',compact('servers'))->with('i', (request()->input('page', 1) - 1) * 5);
    }
    
    public function updateServersFromApi($arrServerList, $arrDbServers, $arrNewServerIds)
    {
        //Insert
        foreach ($arrNewServerIds as $key => $value)
        {
            $req = new Request($arrServerList[$value]);
            Server::create($req->all());
            unset($arrServerList[$value]);
        }
        
        //Update
        foreach ($arrDbServers as $arrDbServer)
        {
            $req = new Request($arrServerList[$arrDbServer['id']]);
            $arrServerServer = $arrDbServer;
            $this->update($req, $arrServerServer);
        }
   
        return;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('servers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required', 
            'description' => 'required'
        ]);
        
        $arrNewServer['name'] = $request->name;
        $arrNewServer['description'] = $request->description;
        $arrNewServer['project'] = 8;
        $arrNewServer['plan'] = 6;
        $arrNewServer['location'] = 2;
        $arrNewServer['os'] = 16;
//        print_r($arrNewServer);
//        exit;
        //Server::create($request->all());
        
        $strBearerToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIyMyIsImp0aSI6IjNmZmY2N2FiODdiMTQ5YjVkY2M1Y2E5OWYyZmIwYWRmZTA4OGVjNTg0MDM0MWFhNjU5YjZmNjM1NDIxODhhZGNiN2YzOGY4Yjk3NjA4ODg4IiwiaWF0IjoxNjgyMzM3NzM4LjU3MzM5MiwibmJmIjoxNjgyMzM3NzM4LjU3MzQwMywiZXhwIjo0ODM4MDExMzM4LjU2MTY1MSwic3ViIjoiNyIsInNjb3BlcyI6W119.m6JKmBopk-Hyw-jDGBltMd4pnASGaqGPdMReTkBn5tmsMWUUPK7eTZ02qw3LgASw6UyIJBrWIhNWF1APq2HxRm_RHSrcCqLvsUPfzLXtbHruiI5Wv47_0fzx6Pzc9O9AKt6hV3gX1-z5Sh-To-uvT3vC57QbNm3UxfIdAeijpN1WbbaYevj311yZa0pj5q3zDdK8WVSefxxl_CF8YeCWQ742tH98k6fSwci_wRcbf_SnLdrzb9sA9V2oitP29_NrbaBwpNdpa_D4H8UWeyNbynxxQslWYDUo13AqU1uS1oCel2oaaetPyMI-72aDQV6bVuQAx4KAOP296fEYseuTRYVfGirsl_eEjZXQYPCat5veO3691M9SvfOQ8CvS4Te9YYO11Fvzn6dawmCeyE45U-FOEUIxWiAl43NA1vv79Rap56uWHLQZN93R-D14QacxouC1D3YjOYsvXhdX3QxCJBVvWHy6PldoX7x8Y8WoOgVZFmbm9k00bloSZWJW0lYnwwYbo5BnWLOIuojwEILzSPVcf5JKngJmWIaoYRdmQmEy5qiudKLT-qiObYjwPhmIE1-R2AChBwmrzQpaPnnD8KY8WduKGe-7U2sDYiog426yCswsFRO3-_wIzdIf0Xrg6--7QczGvKnZqz7cP_h24mP_oqZxYYvVCvPovCqz770';
        $strAuthToken = "Authorization: Bearer " . $strBearerToken;
        
        //Get a list of all the servers on the system
        $strUrl = 'https://solus.ictglobe.co.za:443/api/v1/servers';
                
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $strAuthToken ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_URL, $strUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrNewServer));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        if (!$result)
        {
            die("Connection Failure");
        }
        curl_close($ch);
   
        return redirect()->route('servers.index')->with('success','Server created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Server $server)
    {
        return view('servers.show',compact('server'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Server $server)
    {
        echo 'Roelf disabled this functionality since it was not in the spec';
        return;
        //return view('servers.edit',compact('server'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Server $server)
    {
        $server->update($request->all());
        return;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Server $server)
    {
        $server->delete();
                
        $strBearerToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIyMyIsImp0aSI6IjNmZmY2N2FiODdiMTQ5YjVkY2M1Y2E5OWYyZmIwYWRmZTA4OGVjNTg0MDM0MWFhNjU5YjZmNjM1NDIxODhhZGNiN2YzOGY4Yjk3NjA4ODg4IiwiaWF0IjoxNjgyMzM3NzM4LjU3MzM5MiwibmJmIjoxNjgyMzM3NzM4LjU3MzQwMywiZXhwIjo0ODM4MDExMzM4LjU2MTY1MSwic3ViIjoiNyIsInNjb3BlcyI6W119.m6JKmBopk-Hyw-jDGBltMd4pnASGaqGPdMReTkBn5tmsMWUUPK7eTZ02qw3LgASw6UyIJBrWIhNWF1APq2HxRm_RHSrcCqLvsUPfzLXtbHruiI5Wv47_0fzx6Pzc9O9AKt6hV3gX1-z5Sh-To-uvT3vC57QbNm3UxfIdAeijpN1WbbaYevj311yZa0pj5q3zDdK8WVSefxxl_CF8YeCWQ742tH98k6fSwci_wRcbf_SnLdrzb9sA9V2oitP29_NrbaBwpNdpa_D4H8UWeyNbynxxQslWYDUo13AqU1uS1oCel2oaaetPyMI-72aDQV6bVuQAx4KAOP296fEYseuTRYVfGirsl_eEjZXQYPCat5veO3691M9SvfOQ8CvS4Te9YYO11Fvzn6dawmCeyE45U-FOEUIxWiAl43NA1vv79Rap56uWHLQZN93R-D14QacxouC1D3YjOYsvXhdX3QxCJBVvWHy6PldoX7x8Y8WoOgVZFmbm9k00bloSZWJW0lYnwwYbo5BnWLOIuojwEILzSPVcf5JKngJmWIaoYRdmQmEy5qiudKLT-qiObYjwPhmIE1-R2AChBwmrzQpaPnnD8KY8WduKGe-7U2sDYiog426yCswsFRO3-_wIzdIf0Xrg6--7QczGvKnZqz7cP_h24mP_oqZxYYvVCvPovCqz770';
        $strAuthToken = "Authorization: Bearer " . $strBearerToken;
        $strUrl = 'https://solus.ictglobe.co.za:443/api/v1/servers/' . $server->id;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $strAuthToken ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_URL, $strUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        if (!$result)
        {
            die("Connection Failure");
        }
        curl_close($ch);
  
        return redirect()->route('servers.index')->with('success','Server deleted successfully');
    }
}
