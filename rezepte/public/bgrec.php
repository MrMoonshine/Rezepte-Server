
<?php
  function uploadFile($pname){
    //var_dump($_FILES);
    $uploadOk = 1;
    $target_dir = "/var/www/Rezepte-Server/assets/images/";
    if(strlen(basename($_FILES[$pname]["name"]) < 1)){
      echo ("No image specified OK!");
      return false;
    }
    $target_file = $target_dir . basename($_FILES[$pname]["name"]);
    //echo $target_file;
    //Get MIME type
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    //Check Image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if($check !== false) {
          echo "File is an image - " . $check["mime"] . ".";
          $uploadOk = 1;
        } else {
          echo "File is not an image.";
          $uploadOk = 0;
        }
      }

      // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file <i>$target_file</i> already exists.<br>";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" && $imageFileType != "webp"&& $imageFileType != "jfif") {
      echo "Sorry, only JPG, JPEG, JFIF, PNG & GIF files are allowed.<br>";
      $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        echo "IMG_UPLOAD_ERR Sorry, your image was not uploaded.<br>";
      // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES[$pname]["tmp_name"], $target_file)) {
          echo "The file ". htmlspecialchars( basename( $_FILES[$pname]["name"])). " has been uploaded ";
          echo "to ".$target_file;

          return true;
        } else if($_FILES[$pname]["error"] == 1){
          echo("IMG_UPLOAD_ERR: Filesize exceeded limit of ".ini_get("upload_max_filesize")."!");
          return false;
        } else {
          echo "IMG_UPLOAD_ERR # is ".$_FILES[$pname]["error"]." Unknown Error while uploading ". htmlspecialchars( basename( $_FILES[$pname]["name"]))."<br>";
          return false;
        }
      }
    }

    return false;
?>