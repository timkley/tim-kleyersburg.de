---
date: 2022-11-20
title: Migrate an older Mattermost installation to a new one
hero: hero-image.jpg
excerpt: Maybe you are still using the deprecated MySQL connection or just want to use the new install method.
tags: [mattermost]
image: /articles/img/ogimages/migrate-mattermost.webp
---

I recently had to migrate an older installation of a Mattermost instance to a new server. Unfortunately, the old install method wasn't supported anymore and relied on a MySQL-database, which isn't officially supported anymore.

Since there currently is no direct migration path from MySQL to PostgreSQL which is officially vetted by Mattermost, to following path outlines how you can manually migrate a Mattermost instance by following the steps outlined here.

## Migration outline

1. Upgrade current in use Mattermost instance
2. Setup new server
3. Export data from old server
4. Import data to new server
5. Migrate settings and external integrations
   
### 1. Upgrade current in use Mattermost instance

Your first step should be to make sure that your current instance is using the latest Mattermost version or at least version to which you are migrating. This is important because data exports may be incompatible if you try to import a newer export into an older server or the other way around.

You can find instructions on how to upgrade in the [official Mattermost docs](https://docs.mattermost.com/guides/deployment.html#upgrade-mattermost).

### 2. Setup new server

I've used the [Mattermost Omnibus installation method](https://docs.mattermost.com/install/installing-mattermost-omnibus.html). If you've used another method the results of the following steps may vary. But since we will be using the command line utilities, which come packaged with Mattermost regardless of method of installation, the next steps should work the same.

### 3. Export data from old server

Now you'll use the [mmctl command line tool](https://docs.mattermost.com/manage/command-line-tools.html) to export the data from the old server with the following commands:

First, you'll need to authenticate the command line tools with your server:
```shell
mmctl auth login https://oldmattermosturl.com
```

At this point you should shut off access to the old server so no new data is generated. Do this on the weekend or after-work hours.

The following command will create an export job. The command returns the id of the created export which you can use to check the status of the export.
```shell
mmctl export create
```

Use the `export job show` command to check the status of your export by replacing `{id}` with the id from the previous command.
```shell
mmctl export job show {id}

--- Output
ID: j9fgs4h7htpu0nl14fir3ncsno
Status: success
Created: 2022-10-28 10:55:25 +0000 UTC
Started: 2022-10-28 10:55:31 +0000 UTC
Data: map[include_attachments:true]
```

After the status changes to `success`, use the next command to show all available exports. This will show  you all finished exports.
```shell
mmctl export list

--- Output
j9fgs4h7htpu0nl14fir3ncsno_export.zip

There are 1 exports on https://oldmattermosturl.com/
```

Copy the filename from the previous command to download the export. This will download the file into the current directory from which you've started this command.
```shell
mmctl export download {filename}
```

You should now have the data export on your old Mattermost server. Depending on your infrastructure and if your old and new server have access to each other you will now need to somehow transfer the zip file to the new server.  
In my case I used `rsync` to transfer the data to my laptop:
```shell
rsync -av oldmattermosthost:/path/to/exported-file.zip .
```

### 4. Import data to new server

Using `rsync` again, I transferred the zip file to the new host:
```shell
rsync -av ./exported-file.zip newmattermosthost:/path-where-the-file-should-be
```

Again, the first thing you'll need to do is authenticate the command line tool with on your new server - I'll assume you already installed Mattermost on the new server.
```shell
mmctl auth login https://newmattermosturl.com
```

Depending on how big your data export file is you will need to make a few adjustments on the new instance as well as the server. For our team of around 15 people and an instance of around 18 months old this amounted to a backup size of around 5GB - including attachments, which is much too big for the default settings.

You will have to do 2 things:
- bump the max file upload size in the system console of Mattermost
- update the nginx config to allow bigger upload sizes

Add an admin account and open the system console. You will find the maximum file size setting by either searching for it or navigate to Environment → File Storage → Maximum File Size. Set a maximum file size that is bigger than your backup.

If you don't do this, you will get an error message like this:

> Error: failed to create upload session: Unable to upload file. File is too large.

Next, edit the nginx config file for mattermost (located at `/etc/nginx/conf.d/mattermost.conf`) and configure the `client_max_body_size` to be bigger than your zip file. There are multiple locations where you have to overwrite this setting.  
I used `sed` to replace all occurrences.

If this setting is too small you will get the following error message:

> Error: failed to upload data: AppErrorFromJSON: model.utils.decode_json.app_error  
> 413 Request Entity Too Large

```shell
sed -i -e 's/50M/4G/' /etc/nginx/conf.d/mattermost.conf # replace 4G with what matches your file size
systemctl reload nginx
```

```html +parse
<x-alert>
    Keep in mind that the config file is restored after a reboot or a restart of the Mattermost instance. This is good because you don't need to remember to change this setting back, but you also need to make sure to upload the data before restarting the server.
</x-alert>
```

The next step is to upload the zip file to Mattermost. Although this seems counterintuitive at first glance, it's a necessary step to make the backup available for the import command itself.  
Execute this command with the correct path and filename to your previously `rsync`ed file.

```shell
mmctl import upload exported-file.zip

--- Output
Upload session successfully created, ID: 903p9ttsslrfpryorde2ah25bc
Import file successfully uploaded, name: qjtchzygoalp5whzf4p4ah36nb
```

Depending on the size of the file this could take a while.

Next, run this command to see all available imports. This will return the filename for the following command.
```shell
mmctl import list available

--- Output
903p9ttsslrfpryorde2ah25bc_qjtchzygoalp5whzf4p4ah36nb_export.zip
```

Copy the filename of the file.  
This is the part where your exported data will actually be restored into your new Mattermost instance:

```shell
mmctl import process {filename_from_previous_command}
```

This will take a little while longer than the upload because all of your data is now restored. The job is running in the background but you can check on it with the following command:

```shell
mmctl import job show {id}
```

After the job has finished all your data is restored. Because this only restores you have one last step left for a complete migration.

### 5. Migrate settings and external integrations

There is one problem with this method: since it's not a full database backup, it won't restore your settings and integrations.  
So you will have to manually migrate / setup these things to match your old instance. If you setup new integrations keep in mind that things like webhook urls and API tokens are regenerated, so you need to swap them out in your 3rd-party tools, also.

One last thing I've noticed: user images aren't migrated, too. But this should be manageable :) It gives your users the chance to finally upload a current picture of themselves.
