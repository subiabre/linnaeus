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