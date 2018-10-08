<html>
  <?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/head.php';
  ?>
  <body>
    <form action="upload" method="post" enctype="multipart/form-data">
      Select file to upload:
      <input type="file" name="fileToUpload" id="fileToUpload"/>
      <br>

      Download password (optional):
      <input type="password" name="optionalPassword" id="optionalPassword"/>
      <br>

      Remove file metadata? (Warning: this changes the file checksum!)
      <input type="checkbox" name="removeMetadata" id="removeMetadata" checked/>
      <br>
      
      Maximum file size is 1 GB.
      <input type="submit" name="submitButton" value="Upload"/>
    </form>
  </body>
</html>