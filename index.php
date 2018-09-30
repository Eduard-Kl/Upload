<html>
  <head>
    <title>Upload</title>
  </head>
  <body>
    <form action="upload.php" method="post" enctype="multipart/form-data">
      Select file to upload:
      <input type="file" name="fileToUpload" id="fileToUpload"/>
      <br>
      Password (optional):
      <input type="password" name="optionalPassword" id="optionalPassword"/>
      <br>
      <input type="submit" name="submitButton" value="Upload"/>
    </form>
  </body>
</html>