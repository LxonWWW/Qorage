# Qorage - The simplest storage solution ever

**Do you know this from somewhere?:**

You're looking for something and know roughly where it is but not exactly in which of the full boxes and now you have to open box after box and see where the item is you're looking for?

**Qorage got you covered! A must have for every homelab!**<br>
Just create a storage in Qorage, enter the details and label every box with a QR Code from Qorage. Thats it!<br>
Next time you search for an item just look into Quorage or scan the box to see the contents.

<br>
<br>

![Title Frame](https://cdn.leondierkes.de/images/qorage_title_frame.png)

## Installation

1. Download the repository and place the files on a webserver running PHP.
   (For a quickstart you can use this: https://hub.docker.com/_/php/)
3. Navigate to the folder where you put your files with a webbrowser.
4. *(Optional) If you run a local DNS (eg. Pi Hole) register the URL to a custom domain name (eg. qorage.local).*
5. Thats it, I hope you have fun!

***IMPORTANT: This App is intended to be used in a private environment (eg. Home Lab). Make sure to secure access to the app, if you run it from a publicly accessible location!***

## Usage
- **Creating a new storage:** Enter the URL to Qorage (without a hashtag in the URL) and enter your data and click 'Save Storage'.
- **Deleting a storage:** Click the 'Delete Storage' button.
- **Editing a storage:** Navigate to Qorage and click on 'List' in the top left corner or scan the QR Code from a box and press 'Save Storage'.
- **Printing a QR Code to label a storage:** Create or edit your storage and press the 'QR Code' button. Then click 'Print' afterwards.
