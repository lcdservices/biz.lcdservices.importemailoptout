# biz.lcdservices.importemailoptout

This extension provides a simple mechanism for cycling through emails found in a text file, searching for them in the DB, and marking the contacts "Opt Out (bulk email)" in CiviCRM. It is not truly an importer, as it does not add or alter contacts or emails in the system. It simply marks contacts who have matching emails with the Opt Out flag. The intent is to provide an easy way to update opt-out records in CiviCRM from an external bulk email service.

The extension assumes the following:

* data files are text only and contain one email per line. do not include headers or additional columns. 
* data files are saved in a /data directory inside the extension. you may need to create that directory the first time you use the extension as it is ignored by .git.
* the opt out is handled on the contact level, not per email
* all contacts matching emails in the data file will be updated. for example, if two contacts share the same email and it matches one found in the data file, both will be marked Opt Out

There is no custom interface for using this extension. The functionality is handled in an API function. As such, this could easily be used in another extension/script, triggered from the command line using the cv utility, drush, or other CiviCRM-support command line tool, or be triggered from the API explorer. We will walk through the latter as it is likely the easiest way to use the tool.

After installing and enabling the extension, go to Support > Developer > API Explorer. Select Entity = EmailOptOut and Action = import. The following params are available:

* file (required): file to be processed as found in the extension /data directory. e.g. "MyEmailList.txt"
* limit (optional): number of records to process. If not present, empty, or 0, all rows will be processed. The limit parameter counts based on the text file row, so if multiple contacts have a matching email, they will all be processed.
* group (optional): the ID of a group to remove contacts from during processing.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM 5.x+

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl biz.lcdservices.importemailoptout@https://github.com/FIXME/biz.lcdservices.importemailoptout/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/lcdservices/biz.lcdservices.importemailoptout.git
cv en importemailoptout
```
