
<?php

abstract class UploadError
{
    const OK = 0;
    const ERR_NONE = 1;
    const ERR_FILETYPE = 2;
    const ERR_SIZE = 3;
    const ERR_DUPLICATE = 4;
    const ERR_UNKNOWN = 5;
}

  function uploadFile($pname){
    //var_dump($_FILES);
    $upload_err = UploadError::OK;
    $target_dir = "/var/www/Rezepte-Server/assets/images/";
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
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" && $imageFileType != "webp"&& $imageFileType != "jfif") {
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