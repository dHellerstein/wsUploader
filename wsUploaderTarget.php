<?php
// This is the "target handler" for wsUploader. It receives files sent by a  "source" server
// (that is using wsUploader.php).
// For installation details, see wsUploader_desc.htm
// Note: for better results you may need to tweak  the postMaxSize and  upload_max_filesize in php.ini
//       (perhaps to 6 or 8M)

$targetDirs=[];  // do NOT change this

// =========== user changable variables  ---------------


//$targetDirs['soruceNickname']='fullyQualifiedDir' ; // you can have multiple entries under targertDirs
$targetPwd="" ;                              // the source server's targetPassword must match this
$targetNickname="";                         // used for informational purposes


// --------- end of user changable variables

ob_start()  ;

$rootdir=$_SERVER['DOCUMENT_ROOT'] ;
$nowtime=time();
$curDir=getcwd();
$webRoot=dirname($_SERVER['PHP_SELF']);
$targetServerName=$_SERVER['SERVER_NAME'];

$res=['problem'=>false,'targetServerName'=>$targetServerName,'freeSpace'=>false,'dog'=>'fido'];

$action= readVar('todo',false);
$theSource=readVar('source','');

// note: targetDirs[theSource] is assumed to exist (was checked in step 'check')

if ($action=='basic') {
   $targetDir=$targetDirs[$theSource];
   $targetDir=str_replace('\\','/',$targetDir );
   $targetDir=rtrim($targetDir,'/');

   $targetDirUse=realpath($targetDir);
   if (!is_dir($targetDirUse) ) {
      $res['problem']="No such target directory: $targetDir. This script (".$_SERVER['SCRIPT_FILENAME'].") needs to be modified.";
   } else {
     $res['totalSpace']=disk_total_space($targetDirUse);
     $res['freeSpace']=disk_free_space($targetDirUse);
     $res['targetDir']=$targetDirUse;
     $res['targetNickname']=$targetNickname;
     $res['targetDir_subdirs']=get_subdir_list($targetDirUse);
  }

  $res['memLimit']= getBytes(ini_get('memory_limit'));
  $res['postMaxSize']= getBytes(ini_get('post_max_size'))  ;
  $res['upload_max_filesize']= getBytes(ini_get('upload_max_filesize'));

   ob_end_clean();
   header('Content-type: application/json');
   echo json_encode($res);
   exit;

}

function get_subdir_list($directory,$fullpath=1,&$sublist=[],$origPathLen=0){
    if (func_num_args()<3 || $sublist===0  || $sublist==='0' || $sublist==='') $sublist=[];  // initializse
    $directory=trim($directory);
    $directory=str_replace('\\','/',$directory);
    $directory=rtrim($directory,'/');
    $adir=$directory.'/*';  // avoid . and ..
    if ($origPathLen==0 ) {
       $origPathLen=strlen($directory) ;
       $origFullPath= realpath($directory);
       $origFullPath= str_replace('\\','/',$origFullPath);
        if ($fullpath==0) {
              $sublist[]= $directory;
           } else if ($fullpath==2) {
              $sublist[]=[$directory,$origFullPath] ;
           } else  {  // all else is full path
              $sublist[]= $origFullPath ;
           }
    }
    $files = glob($adir);
    foreach($files as $path){
        if (is_dir($path)) {
           if ($fullpath==0) {
              $sublist[]= substr($path,$origPathLen+1) ;
           } else if ($fullpath==2) {
              $sublist[]=[realpath($path),substr($path,$origPathLen+1)];
           } else  {  // all else is full path
              $sublist[]= realpath($path);
           }
           get_subdir_list($path,$fullpath,$sublist,$origPathLen);
        }
    }
    return $sublist;
}

// ------   http://stackoverflow.com/questions/4371059/shorten-long-numbers-to-k-m-b
// convert a nunber to k,m.
// add commas to numberic part. Deafult 1 decimal points of accuracy; switch to K,M,B at >1000, >1million, >1 billion
// ismult is used to set the switch points. I.e.; if ismult=10, then switch at >10k, >10m, and >10G
//           (so 1312 becomes 1,312 while 13124 becomes 13.1K
// you can add stuff before and after the K, etc.
//   pre: add after number and before K,M,G
//  post: add after number and after K,M,G
// Note these are added even for small numbers
// ie: pre=' * ', post=' bytes', then custom_number_format(123456,1,1,'* ','bytes') yields "123.4 * k bytes"
//
// precision is # of decimal points to show. IF a an array (0...3) then # of decmimal points depends on size of value

function custom_number_format($n, $precision = 1,$ismult=1,$pre='',$post='') {

   $thePrecs=$foo=array_fill(0,4,$precision);      // 0:<1000 *ismult, 1 =< 1,000,000*ismult, 2 <= 100,0000,000 * ismult; 3 > 100,0000,000 * ismult;
    if (is_array($precision))  {    // use precision that depends on value
      for ($i=0;$i<count($thePrecs);$i++) {
          if (array_key_exists($i,$precision) && is_numeric($precision[$i]))  $thePrecs[$i]=intval($precision[$i]);
      }
    }
    if ($n < 1000*$ismult) {           // Anything less than a thousand (add commas)
        $n_format = number_format($n,$thePrecs[0]) . $post ;
    } else if ($n < 1000000*$ismult) {         // lt 1 million, gt 10k
        $n_format = number_format($n / 1000, $thePrecs[1]) . $pre. 'K' . $post ;
    } else if ($n<1000000000*$ismult) {                // gt 1 million lt 10 billion
        $n_format = number_format($n / 1000000, $thePrecs[2]) .  $pre. 'M' . $post ;
    } else {
        $n_format = number_format($n / 1000000000, $thePrecs[3]) .  $pre. 'G' . $post ;
    }
    return $n_format;
}

// ================
// takes a value from ini_get(), and converts to an integer (i.e.; 8M becomes 8388608)
 function getBytes($val) {
    $val = trim($val);
    preg_match('/([0-9]+)[\s]*([a-zA-Z]+)/', $val, $matches);
    $value = (isset($matches[1])) ? intval($matches[1]) : 0;
    $metric = (isset($matches[2])) ? strtolower($matches[2]) : 'b';
    switch ($metric) {
        case 'tb':
        case 't':
            $value *= 1024;
        case 'gb':
        case 'g':
            $value *= 1024;
        case 'mb':
        case 'm':
            $value *= 1024;
        case 'kb':
        case 'k':
            $value *= 1024;
    }
    return $value;
}
// =============
// create directories
if ($action=='setDirs') {
 $logonUse=readVar('logon','');

 session_id($logonUse);
 session_start() ;
 if (!isset($_SESSION['uploaderS_logon'])) {
      print "Logon required (setDirs) -- source and target servers must use same password ";
      exit;
  }
  $dirList0=readVar('dirListUse',[]);
  $dirList1=explode(',',$dirList0);
  sort($dirList1,SORT_STRING );
  $res1=['fail'=>[],'exist'=>[],'create'=>[]];

   $targetDir=$targetDirs[$theSource];
   $targetDir=str_replace('\\','/',$targetDir );
   $targetDir=rtrim($targetDir,'/');

   $targetDirUse=realpath($targetDir);

  foreach ($dirList1 as $ii=>$adir0) {

     if (strpos($adir0,'..')!==false) {
         print "Illegal directory name (contains ..) ";
         exit;
     }
     $adir=str_replace('\\','/',$adir0);
     $adir=trim($adir,'/');
     $dodir= ($targetDirUse.'/'.$adir);
     if (is_dir($dodir)) {
        $res1['exist'][]=$adir0;
     } else {
        $amess="creating: $adir0 ($dodir)" ;
        $qok= @mkdir($dodir,0777,true);
        if (is_dir($dodir)) {
          $res1['create'][]=$adir0;
        } else {
          $res1['fail'][]=$adir0;
        }
     }    // if isdir
   }   // firesch
   $a1=implode(',',$res1['exist']);
   $a2=implode(',',$res1['create']);
   $a3=implode(',',$res1['fail']);
   $a4=$a1.';'.$a2.';'.$a3;        // exist ;create ;fail
   print $a4 ;
   exit;
}

//====== check if files already exists
if ($action=='checkFiles') {
 $logonUse=readVar('logon','');

 session_id($logonUse);
 session_start() ;

 $targetDir=$targetDirs[$theSource];
 $targetDir=str_replace('\\','/',$targetDir );
 $targetDir=rtrim($targetDir,'/');

 $targetDirUse=realpath($targetDir);

 if (!isset($_SESSION['uploaderS_logon'])) {
      print "Logon required (setDirs) -- source and target servers must use same password ";
      exit;
  }
  $fileListUse0=readVar('fileListUse',[]);
  $fileListUse=explode(',',$fileListUse0);
  $existFile=[]; $notExistFile=[]; $existFileOld=[];

  foreach ($fileListUse as $ii=>$afile0) {
     $arf=explode(';',$afile0);
     $jorig0=$arf[0];
     $arf2=explode(' ',$jorig0);
     $jorig=trim($arf2[0]);
     $mdateSource=trim($arf2[1]);
     $afile=$arf[1];
     $dofile= ($targetDirUse.'/'.$afile);
     if (is_file($dofile)) {
         $mdate=filemtime($dofile);
         if ( ($mdateSource-$mdate)>2) {   // filemtime has iffy resolution
            $existFileOld[]=$jorig;
        } else {
            $existFile[]=$jorig;
        }

     } else {
         $notExistFile[]=$jorig;
     }
  }
   $a0=implode(',',$existFile);
   $a1=implode(',',$existFileOld);
   $a2=implode(',',$notExistFile);
   print  $a0.';'.$a1.';'.$a2 ;  //  exist;existOld;notexist
   exit;
 }

// return a salt to the source (for use in password enctyption)
if ($action=='check') {

  if (count($targetDirs)<1 || trim($targetPwd)=='' ||  trim($targetNickname)=='') {
    print  "targetDirs or targetPwd or targetNickname have not been specified. This script (".$_SERVER['SCRIPT_FILENAME'].") needs to be modified.";
    exit;
  }

  if (!array_key_exists($theSource,$targetDirs)){
      print "Unrecognized source server ($theSource). This script (".$_SERVER['SCRIPT_FILENAME'].") needs to be modified." ;
      exit;
  }
  $asource=readVar('source',false);
    $time=time();
    $arand=substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"), 10, 10);
    $asalt= $time."_".$arand ;
   print "$asource,$asalt";
   exit;
}

// validation  from source
if ($action=='check2') {

  session_start() ;
  $sentPwd=readVar('pwdHash','');
  $asalt=readVar('salt','');
  $sourceNickName=trim(readVar('source',''));

  $goo=explode('_',$asalt);
  $atime=$goo[0] ;
  $nowTime=time();
  $adiff=abs($nowTime-$atime);
  if ($adiff>100) {    // 100 second lifespan for this hashed password
     print "0,timeout  ";
     exit;
  }
  $myHash=md5($targetPwd.'_'.$asalt);
  if ($sentPwd==$myHash) {
   //  $useLogon='uploaderS_'.$sourceNickName;
    $useLogon='uploaderS_logon';
     $_SESSION[$useLogon]= $atime ;
     $ss=session_id();
     print "1,$sourceNickName,$ss,$targetNickname";
     exit;
  }
  print "0,mismatch" ;
  exit;
}



// -----------
// get a zip file and save its contents to dir

if ($action=='saveSetZip') {
 $logonUse=readVar('logon','');
 $sayFile=readVar('sayFile','');
 session_id($logonUse);
 session_start() ;
 if (!isset($_SESSION['uploaderS_logon'])) {
      print "Logon required -- source and target servers must use same password ";
      exit;
  }
   $targetDir=$targetDirs[$theSource];
   $targetDir=str_replace('\\','/',$targetDir );
   $targetDir=rtrim($targetDir,'/');

  $targetDirUse=realpath($targetDir);
  $ufileZip=$_FILES['fileData']['tmp_name'];


   $zip = new \ZipArchive(); // Load zip library
   $res=$zip->open($ufileZip  );
   $nfiles=$zip->numFiles;
   $acomment=$zip->comment;
   print "Saving $sayFile (<em>$acomment</em>) " ; // with $nfiles files ";

     for ($jz=0;$jz<$nfiles;$jz++) {
      $garg=$zip->statIndex($jz);
      $fileName=trim($garg['name']);
      if (strpos($fileName,'..')!==false) {
         print '<br>Illegal fileName (contains ..) ';
         continue ;
      }
      $fileNameSave=str_replace('\\','/',$fileName );
      $fileNameSave=trim($fileNameSave,'/');
      $fileNameSave= ($targetDirUse.'/'.$fileNameSave);

      $fileMtime=$garg['mtime'];

      $gstuff=$zip->getFromIndex($jz);
      $qq=file_put_contents($fileNameSave,$gstuff);
       print "<br> $fileName: $qq " .' ('.date('Y-M-d H:i ',$fileMtime).')';
      touch($fileNameSave,$fileMtime);

    }
    exit;
}



// -----------
// get a piece of file and save its contents to dir

if ($action=='saveSetPiece') {

   $logonUse=readVar('logon','');
    $sayFile=readVar('sayFile','');
   session_id($logonUse);
   session_start() ;
   if (!isset($_SESSION['uploaderS_logon'])) {
      print "Logon required -- source and target servers must use same password ";
      exit;
  }

   $targetDir=$targetDirs[$theSource];
   $targetDir=str_replace('\\','/',$targetDir );
   $targetDir=rtrim($targetDir,'/');

  $targetDirUse=realpath($targetDir);
  $upieceZip=$_FILES['fileData']['tmp_name'];

  $piece=readVar('piece',0);
  $npieces=readVar('npieces',0);
   $sayFile=trim(readVar('sayFile',''));
   if (strpos($sayFile,'..')!==false) {
       print "Error: illegal file name (contains ...) ";
       exit;
   }

  $fileNameSave=$targetDirUse.'/'.$sayFile;

  $zip = new \ZipArchive(); // Load zip library
  $res=$zip->open($upieceZip);
  $garg=$zip->statIndex(0);
  $fileMtime=$garg['mtime'];
  $gstuff=$zip->getFromIndex(0);
  $klen=strlen($gstuff);
  if ($piece==1)  {
      $jadd=file_put_contents($fileNameSave,$gstuff);
      print custom_number_format($jadd)." (first of $npieces) saved to $sayFile " ;
  } else {
      $jadd=file_put_contents($fileNameSave,$gstuff,FILE_APPEND);
      if ($piece==$npieces) {
           touch($fileNameSave,$fileMtime);
           $sayDate=date('Y-M-d H:i ',$fileMtime);
           print custom_number_format($jadd)." (last of $npieces) saved to $sayFile ($sayDate)" ;
      } else {
          print custom_number_format($jadd)." ($piece/$npieces) saved to $sayFile " ;
      }
  }

  exit;

}

print "No such action: $action " ;


exit ;  // should never get here

function readVar($varname,$adef=false) {
  $varname=trim(strtoupper($varname));

  $cval3=trim($varname);   // getwords
  $cval4=preg_replace("/[\t\n\r\s,]+/"," ", $cval3) ;             // collapse spaces and other seperators
  $vars=explode(' ',$cval4) ;

  foreach ($_REQUEST as $vname=>$oof) {
     $avar=trim(strtoupper($vname)) ;
     for ($ith=0;$ith<count($vars);$ith++) {
        if ($avar==$vars[$ith]) {
           return $oof ;
        }
     }
  }
// if here, could not be found
 return $adef ;
}
?>