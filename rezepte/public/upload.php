
<?php

abstract class UploadError
{
    const OK = "OK";
    const ERR_NONE = "Keine Datei.";
    const ERR_FILETYPE = "Datei ist vom falschen Typ!";
    const ERR_SIZE = "Datei ist zu Groß!";
    const ERR_DUPLICATE = "Datei gibt es schon.";
    const ERR_UNKNOWN = "Unbekannter Fehler.";
}

abstract class UploadMimetype
{
  const IMAGE = ["png", "jpg", "jpeg", "gif", "webp", "jfif"];
  const TAR_GZ = ["tar.gz", "gz"];
}
  // ift = ist-filetype, sft = soll-filetype[UploadMimetype]
  function verfiyMimeType($ift, $sft){
    $matches = 0;
    foreach($sft as &$ex){
      if($ex == $ift){
        $matches++;
      }
    }
    unset($ex);
    return $matches > 0;
  }

  function uploadFile($pname, $target_dir, $mimetype = UploadMimetype::IMAGE){
    //var_dump($_FILES);
    $upload_err = UploadError::OK;
    if(strlen(basename($_FILES[$pname]["name"]) < 1)){
      //echo ("No image specified OK!");
      return UploadError::ERR_NONE;
    }
    $target_file = $target_dir . basename($_FILES[$pname]["name"]);
    //echo $target_file;
    //Get MIME type
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    //Check Image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if($check !== false) {
          //echo "File is an image - " . $check["mime"] . ".";
          $upload_err = UploadError::OK;
        } else {
          //echo "File is not an image.";
          $upload_err = UploadError::ERR_FILETYPE;
        }
      }

      // Check if file already exists
    if (file_exists($target_file)) {
        //echo "Sorry, file <i>$target_file</i> already exists.<br>";
        $upload_err = UploadError::ERR_DUPLICATE;
    }

    // Allow certain file formats
    if(verfiyMimeType($imageFileType, $mimetype)) {
      //echo "Sorry, only JPG, JPEG, JFIF, PNG & GIF files are allowed.<br>";
      $upload_err = UploadError::ERR_FILETYPE;
    }

    if ($upload_err != UploadError::OK) {
        //echo "IMG_UPLOAD_ERR Sorry, your image was not uploaded.<br>";
        return $upload_err;
      // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES[$pname]["tmp_name"], $target_file)) {
          error_log("<p>The file ". htmlspecialchars( basename( $_FILES[$pname]["name"])). " has been uploaded to ".$target_file."</p>", 1);
          $upload_err = UploadError::OK;
        } else if($_FILES[$pname]["error"] == 1){
          //echo("IMG_UPLOAD_ERR: Filesize exceeded limit of ".ini_get("upload_max_filesize")."!");
          $upload_err = UploadError::ERR_SIZE;
        } else {
          //echo "IMG_UPLOAD_ERR # is ".$_FILES[$pname]["error"]." Unknown Error while uploading ". htmlspecialchars( basename( $_FILES[$pname]["name"]))."<br>";
          $upload_err = UploadError::ERR_UNKNOWN;
        }
      }
      return $upload_err;
    }
?>