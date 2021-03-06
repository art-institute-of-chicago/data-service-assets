import imagehash

import PIL
import numpy as np
import imutils
import cv2
import scipy.misc.pilutil

# Helper to parse CSV cells into booleans
# https://stackoverflow.com/questions/31842424/boolean-value-of-fields-in-csv-file-in-python
def get_bool(value):
    return True if value == '1' else False

# Helper to load images, optimized for pyramidal TIFFs
def get_pil_image(file, min_width=None):
    try:
        pil_img = PIL.Image.open(file)
    except (PIL.UnidentifiedImageError, PIL.Image.DecompressionBombError):
        return None

    # https://github.com/python-pillow/Pillow/pull/3227
    # https://pillow.readthedocs.io/en/stable/handbook/image-file-formats.html#reading-multi-frame-tiff-images
    if getattr(pil_img, 'is_animated', False):
        if min_width is None:
            pil_img.seek(pil_img.n_frames - 1)
        else:
            for frame in range(pil_img.n_frames - 1, 0, -1):
                pil_img.seek(frame)
                if (pil_img.width > min_width):
                    break

    # OSError: image file is truncated (0 bytes not processed)
    try:
        pil_img.load()
    except (IOError, OSError):
        pil_img.close()
        return None

    return pil_img

# https://realpython.com/blog/python/fingerprinting-images-for-near-duplicate-detection/
# https://github.com/JohannesBuchner/imagehash
def get_image_fingerprint(file, row):
    do_ahash = get_bool(row['ahash'])
    do_phash = get_bool(row['phash'])
    do_dhash = get_bool(row['dhash'])
    do_whash = get_bool(row['whash'])

    if not do_ahash and not do_phash and not do_dhash and not do_whash:
        return None, None, None, None

    pil_img = get_pil_image(file)

    if pil_img is None:
        return None, None, None, None

    ahash = str(imagehash.average_hash(pil_img)) if do_ahash else None
    phash = str(imagehash.phash(pil_img)) if do_phash else None
    dhash = str(imagehash.dhash(pil_img)) if do_dhash else None
    whash = str(imagehash.whash(pil_img)) if do_whash else None

    pil_img.close()

    return ahash, phash, dhash, whash

# https://www.pyimagesearch.com/2017/06/05/computing-image-colorfulness-with-opencv-and-python/
def get_image_colorfulness(file, row):
    if not get_bool(row['colorfulness']):
        return None

    pil_img = get_pil_image(file, min_width=250)

    if pil_img is None:
        return None

    cv_img = scipy.misc.fromimage(pil_img)

    pil_img.close()

    if cv_img is None:
        return None

    if pil_img.width > 500:
        cv_img = imutils.resize(cv_img, width=250)

    # split the image into its respective RGB components
    # ValueError: too many values to unpack (expected 3)
    try:
        (B, G, R) = cv2.split(cv_img.astype('float'))
    except ValueError:
        return None

    # compute rg = R - G
    rg = np.absolute(R - G)

    # compute yb = 0.5 * (R + G) - B
    yb = np.absolute(0.5 * (R + G) - B)

    # compute the mean and standard deviation of both `rg` and `yb`
    (rbMean, rbStd) = (np.mean(rg), np.std(rg))
    (ybMean, ybStd) = (np.mean(yb), np.std(yb))

    # combine the mean and standard deviations
    stdRoot = np.sqrt((rbStd ** 2) + (ybStd ** 2))
    meanRoot = np.sqrt((rbMean ** 2) + (ybMean ** 2))

    # derive the "colorfulness" metric
    colorfulness = stdRoot + (0.3 * meanRoot)

    return colorfulness
