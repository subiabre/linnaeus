# linnaeus

`linnaeus` is a small command line tool to help me organize my photography collection.

It scans a folder, filters through images, reads them and allows the user to automate the sorting process of the images by using a YAML based configuration and reading the metadata in the images.

It can read the following variables from an image:

`{image.*}` contains image specific metadata
- `{image.type}` MIME type as specified by the image
- `{image.width}` X size of the image in pixels, as specified by EXIF data or the file itself
- `{image.height}` Y size of the image in pixels, as specified by EXIF data or the file itself
- `{image.author}` Name of the credited artist if present, if not will be just a blank string: ""
- `{image.camera}` Name of the credited camera model if present, if not will be just a blank string: ""

`{date.*}` is read from the image creation date as specified by the EXIF data or if not present, the date the file was last modified
- `{date.year}`
- `{date.month}`
- `{date.day}`
- `{date.hour}`
- `{date.minutes}`
- `{date.seconds}`

`{file.*}` contains file generic metadata
- `{file.name}` Name of the file
- `{file.extension}` File extension

## Usage
Main command is `assort`. This command takes a linnaeus configuration file using the following precedence:

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

`assort` will take all the image files in a *source* folder and move or copy them to a *target* folder where the input files will be renamed using the structure specified at configuration `output.naming.files` and moved into folders inside the target folder using the structure specified at configuration `output.naming.folders`.
