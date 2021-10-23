# linnaeus

`linnaeus` is a small command line tool to help me organize my photography collection.

It scans a folder, filters through images, reads them and allows the user to automate the sorting process of the images by using a YAML based configuration and reading the metadata in the images.

It can read the following variables from an image:
- `{imageType}` MIME type as specified by the image
- `{fileName}` Name of the file
- `{fileExtension}` File extension
- `{year}` The year the image was taken or if not present, the year the file was created
- `{month}` The month the image or the file were created at
- `{day}` The day the image or the file were created at
- `{hour}` The hour the image or the file were created at
- `{minutes}` The minutes the image or the file were created at
- `{seconds}` The seconds the image or the file were created at

## Usage
Main command is `sort`. This command takes a linnaeus configuration file using the following precedence:

1. File provided via option `--configuration`
2. File `linnaeus.yaml` located at source folder.
3. File `linnaeus.yaml` located at linnaeus installation folder.

This file can specify the following keys:
```yaml
input:
    copyFiles: # Should the original files be copied from source to remote folder? If set to false, the files at source will be moved out of source.
output:
    naming:
        files: # File naming structure
        folders: # Folder naming structure
```

`sort` will take all the image files in a *source* folder and move or copy them to a *target* folder where the input files will be renamed using the structure specified at configuration `output.naming.files` and moved into folders inside the target folder using the structure specified at configuration `output.naming.folders`.
