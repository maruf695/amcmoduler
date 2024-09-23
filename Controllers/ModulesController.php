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
         
        
    }

    public function create(){
       
    }

    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        

    }

    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function versionView($id)
    {
       
      
    }

    public function edit($id)
    {
        
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
       
    }

    
}

