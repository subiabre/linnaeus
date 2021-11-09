# linnaeus

`linnaeus` is a small command line tool to help me organize my photography collection.

It scans a folder, filters through images, reads them and allows the user to automate the sorting process of the images by using a YAML based configuration and reading the metadata in the images.

It can read the following variables from an image:

`{image.*}` contains image specific metadata
- `{image.type}` MIME type as specified by the image
- `{image.width}` X size of the image in pixels, as specified by EXIF data or the file itself
- `{image.height}` Y size of the image in pixels, as specified by EXIF data or the file itself
- `{image.author}` Name of the credited artist if present, or a blank string: ""
- `{image.camera}` Name of the credited camera model if present, or a blank string: ""

`{date.*}` is read from the image creation date as specified by the EXIF data or if not present, the date the file was last modified. All dates are returned as their numeric value.
- `{date.year}` e.g: 2021
- `{date.month}` e.g: 01
- `{date.day}` e.g: 27
- `{date.hour}` e.g: 23
- `{date.minutes}` e.g: 40
- `{date.seconds}` e.g: 33

`{file.*}` contains file generic metadata
- `{file.name}` Name of the file
- `{file.extension}` File extension
- `{file.hash}` Complete file SHA256 hash string

## Usage
To run linnaeus: `bin/linnaeus`. This command takes a linnaeus configuration file using the following precedence:

1. File provided via option `--configuration`
2. File `linnaeus.yaml` located at the execution folder.
3. File `linnaeus.yaml` located at linnaeus installation folder.

This file can specify the following keys:
```yaml
input:
    # Should the original files be copied from source to remote folder? If set to false, the files at source will be moved out of source.
    copyFiles: true
output:
    # The number of characters at the start of {file.hash} to include in the final filename
    fileHashLength: 6
    naming:
        # File taxonomy, if too long for a single line you can split it in an array
        files: ""
        # Folder taxonomy, if too long for a single line you can split it in an array
        folders: ""
```

`bin/linnaeus` will take all the image files in a *source* folder and move or copy them to a *target* folder where the input files will be renamed using the structure specified at configuration `output.naming.files` and moved into folders inside the target folder using the structure specified at configuration `output.naming.folders`.
