<?php
/** 
 * 配置文件操作(查询了与修改) 
 * Use JSON format to read in the configurations.
 * 
 */
//Read in the config info with JSON data format
function readConfigJSON($in_file)
{
  if (! file_exists($in_file)){
    printf("Input file %s cannot be opened!\n", $in_file);
    return false;
  }
  else{
    //read in the content as a string
    $content = file_get_contents($in_file);
 
    //look for DEV
    $data = json_decode($content);

    return $data;
  }
}

//Write the JSON format configuration to a file
function writeConfigJSON($out_data, $out_file)
{
  //Check if the output file name is a String
  //and can be opened.
  if ( is_string($out_file) ){
    $file_handle = fopen($out_file, "w+");
    //Open file for overwrite
    if ( flock($file_handle, LOCK_EX)){
      //do an exclusive lock
      if ( fwrite($file_handle, json_encode($out_data)) == false)
        echo "Error in write out the file!";
    }
    flock($file_handle, LOCK_UN);
    //release the file lock
    fclose($file_handle);
  }
}

//Read in test data in JSON data format
function readTestData($in_file)
{
  if (! file_exists($in_file)){
    printf("Input file %s cannot be opened!\n", $in_file);
    return false;
  }
  else{
    $json_content = file_get_contents($in_file);
    return json_decode($json_content);
  }
}

