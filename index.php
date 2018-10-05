<html>
  <head>
    <title>Upload</title>
  </head>
  <body>
    <form action="upload.php" method="post" enctype="multipart/form-data">
      Select file to upload:
      <input type="file" name="fileToUpload" id="fileToUpload"/>
      <br>

      Download password (optional):
      <input type="password" name="optionalPassword" id="optionalPassword"/>
      <br>

      Remove file metadata? (Warning: this changes the file checksum!)
      <input type="checkbox" name="removeMetadata" id="removeMetadata" checked/>
      <br>
      
      <input type="submit" name="submitButton" value="Upload"/>
    </form>
  </body>
</html>