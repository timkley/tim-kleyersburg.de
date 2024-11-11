---
date: 2022-08-09
title: Use ImageMagicks mogrify CLI to batch resize images
hero: hero-image.jpg
excerpt: A quick way to resize images to the same canvas size
tags: [quicktip]
image: /articles/img/ogimages/resize-images-with-mogrify.webp
---

When working with an ecommerce client, I had the requirement to display images in uniform boxes. This was way before we had things like [object-fit](https://caniuse.com/object-fit). The big problem was: the images the client provided where far from uniform. Some images were landscape oriented, others in portrait, some were squared. They also differed in file size and type, some being over 5MB in size, others the opposite.

I wanted a quick way to resize all these images to one output format, defined as the following:

* end image should be 500 x 500 pixels
* background should always be white
* images should have a slight border of 15 pixels
* images should be centered

For this task I used the free software [ImageMagick](https://imagemagick.org/), specifically its CLI tool [mogrify](https://imagemagick.org/script/mogrify.php)

On Mac you can use `brew` to install the CLI with `brew install imagemagick`.

Our command will look like this:

```shell
mogrify -path processed -trim -resize "470x470>" -gravity center
-extent 500x500 -background white -format jpg "*"
```

Let's break this command down bit by bit:

`-path`: Path where the image should be put out ("processed" in this case)
`-trim`: Cut the image on all sides so not whitepsace is left (if the image sits on white or transparent background)
`-resize`: width x height, `>` means the longest side will be used for resizing, the other side will be resized proportionally
`-gravity`: Parameter order is important as this affects the next parameter (extent). Defines how extent works
`-extent`: Extends the image to 500 x 500 pixels, our final format. Since `gravity` is set to `center` it gets extended equally on all 4 sides.
`-background`: Define a background color
`-format`: Defines the format
`"*"`: Double quotes ensure that the wildcard glob is not expanded by the shell, but mogrify itself.

If you now run this command in a folder with all your images you will have perfectly square images which can be directly used without any CSS tricks.
