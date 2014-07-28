<?php
/**
 * This file is part of Choqled PHP Framework and/or part of a BFLDEV Software Product.
 * This file is licensed under "GNU General Public License" Version 3 (GPL v3).
 * If you find a bug or you want to contribute some code snippets, let me know at http://bfldev.com/nreeda
 * Suggestions and ideas are also always helpful.

 * @author Roland Eigelsreiter (BrainFooLong)
 * @product nReeda - Web-based Open Source RSS/XML/Atom Feed Reader
 * @link http://bfldev.com/nreeda
**/

/**
* English translation
*/

$translations = array(
    "iewarning" => "This application has not been tested and desigend for Internet Explorer lower than version 9 (or Version with compatibility mode). It's really recommended to NOT use this old version of Internet Explorer.",
    "event.1" => "Start update for {FEED}",
    "event.2" => "Update for {FEED} successfully",
    "event.3" => "Cronjob started",
    "event.4" => "Cronjob finished",
    "event.5" => "Could not find a matching parser for {FEED}{TEXT}. Maybe the file is malformed or we don't have implemented the file type",
    "event.6" => "Could not fetch content from this url: {TEXT}",
    "event.7" => "Could not parse the XML data from: {TEXT}",
    "event.8" => "Feed {TEXT} already exist, import canceled",
    "event.9" => "Aliens disrupting: {TEXT}",
    "event.10" => "OPML successfully imported",
    "event.11" => "Feed {FEED} successfully added",
    "event.12" => "File successfully imported",
    "uncategorized" => "Uncategorized",
    "user.1" => "Administrator",
    "user.2" => "User",
    "form.validator.required" => "Is required",
    "form.validator.maxlength" => "Maximal length: %s characters",
    "feedadmin.raw.1" => "RAW Content",
    "feedadmin.raw.2" => "The original RAW Feed Content before output formatting",
    "feedadmin.format.1" => "Formated Content",
    "feedadmin.format.2" => "The content that you will see at the end, after JS execution.",
    "feedadmin.js.1" => "Javascript Content Modification (for all feeds from \"%s\")",
    "feedadmin.js.2" => 'Execute any Javascript Code that you want on the RAW HTML of the content.
        The variable "html" is  available to modify the content. jQuery can also be used on the html.
        Example (Remove every %s tag in the html): <b>%s</b>',
    "archive.title" => "News Archive",
    "archive.1" => "Start from",
    "archive.2" => "End at",
    "archive.3" => "Search in Archive",
    "mark.all.category" => "Mark all news in this view as read",
    "end.newssection.1" => "End of this news section",
    "end.newssection.2" => "To search for older/read entries - Goto the archive",
    "found.news" => "Found %s News",
    "wait.check" => "Please wait... We search for more entries...",
    "mark.read" => "Mark as read",
    "saveit" => "Save It",
    "remove.save" => "Remove from my list",
    "feed" => "Feed",
    "category" => "Category",
    "url" => "URL",
    "adminview" => "Adminview",
    "dashboard" => "Dashboard",
    "note.opml.import" => "You already use another reader service? Check out the OPML Import to quick setup this reader!",
    "note.addfeed" => "Want to add a new feed to your collection? Click on the plus icon on the top right of your sidebar!",
    "note.bug" => "Found a bug? Have a suggestion? Visit https://bfldev.com/nreeda",
    "note.opml.export" => "Want to leave this reader? Export your feeds into a OPML file!",
    "note.search" => "You remember a news that you want read again? Search it by clicking on the glass on the top right of your sidebar!",
    "note.settings" => "Customize your reader, click the settings icon on the top right of your sidebar!",
    "dashboard.eventlog" => "Event Log - For Admins",
    "dashboard.clearlog" => "Clear Log",
    "reader.installed" => "Reader already installed",
    "install.folder.notexist" => "Folder '%s' does not exist - You must create it",
    "install.folder.writeable" => "Folder '%s' is not writeable - Set correct CHMOD or remove write protection",
    "install.php.feature" => "You must enable/install the PHP feature/function/option/extension '%s'",
    "install.db" => 'MySQL Database Name',
    "install.host" => 'MySQL Database Host',
    "install.user" => 'MySQL Username',
    "install.pw" => 'MySQL Password',
    "install.admin.user" => 'Administrator Username',
    "install.admin.pw" => 'Administrator Password',
    "install.finish" => 'Finish Installation',
    "install.warn" => '* All existing tables, that need to be created during installation, will be deleted',
    "ok" => "OK",
    "login.1" => "Username and/or Password do not match",
    "login.2" => "Stay logged in?",
    "username" => "Username",
    "password" => "Password",
    "organize.1" => "Type in new category name",
    "organize.2" => "Save new Category",
    "organize.3" => "Manage your Categories",
    "organize.4" => "Import from a file",
    "organize.5" => 'This import allow you to import a list of feeds from other reader services.
            You can upload a OPML file or a line based (each line a feed URL) file',
    "organize.6" => "Importing... Please wait...",
    "organize.7" => "OPML / File Export",
    "organize.8" => "Download a list of all your feeds in a OPML or line based file. This allow you to import all feeds in another reader",
    "organize.9" => "OPML Download",
    "organize.10" => "Textfile Download",
    "organize.11" => "Move this feed into another category",
    "organize.12" => "Move",
    "organize.13" => "Are you sure? This cannot be undone!",
    "organize.14" => "Are you sure? All feeds in the given category will also be deleted!",
    "organize.15" => "Delete",
    "organize.16" => "Manage your Feeds",
    "upload" => "Upload",
    "rss.1" => 'RSS Feed Export',
    "rss.2" => 'With nReeda you are also be able to export RSS feeds from your own categories. Just configure the output and than you can use the feed as every other rss feed.',
    "rss.3" => 'Choose Categories and Feeds',
    "rss.4" => 'RSS Title',
    "rss.5" => 'RSS Description',
    "rss.6" => 'Max. total news',
    "rss.7" => 'Max. News per Category (0 = unlimited)',
    "rss.8" => 'Max. News per Feed (0 = unlimited)',
    "rss.9" => 'Generate RSS',
    "rss.10" => 'Only numbers',
    "saved" => "Saved",
    "settings.1" => "Settings",
    "settings.2" => "Change your Password",
    "settings.3" => "Re-Type Password",
    "yes" => "Yes",
    "no" => "No",
    "sidebar.1" => "Add Feed",
    "sidebar.2" => "Import from File",
    "sidebar.3" => "Search for a News Title",
    "sidebar.4" => "Reader Settings",
    "sidebar.5" => "Show images",
    "sidebar.6" => "Default",
    "sidebar.7" => "Big Articles",
    "sidebar.8" => "Just Headlines",
    "sidebar.9" => "Mark as read on scroll down",
    "sidebar.10" => "All News",
    "sidebar.11" => "My News",
    "sidebar.12" => "My Account",
    "sidebar.13" => "Organize",
    "sidebar.14" => "Settings",
    "sidebar.15" => "RSS Export",
    "sidebar.16" => "Administration",
    "sidebar.17" => "System Tasks",
    "sidebar.18" => "System Settings",
    "sidebar.19" => "Users",
    "sidebar.20" => "Bye Bye",
    "sidebar.21" => "Search",
    "sidebar.22" => "Add Feed by URL",
    "sidebar.23" => "Small Articles",
    "sidebar.24" => "Pro Tip: ",
    "sidebar.25" => "Browser Script for more comfort",
    "admin.settings.1" => "System Settings",
    "admin.settings.2" => "Delete entries older than...",
    "admin.settings.3" => "Delete eventlog older than...",
    "admin.settings.4" => "Never",
    "months" => "Month/s",
    "years" => "Year/s",
    "days" => "Day/s",
    "weeks" => "Week/s",
    "admin.update.1" => "Automated Feed Update",
    "admin.update.2" => 'To automate your feed update process you need to call a specific update URL periodically.
            You can do this with tasks or "cronjobs".
            If you don\'t have access to such system relevent tools you can use some free services as well.
            Here is a list of services that allow you to manage tasks free and online: %s

            Your URL for automatic updates (Notice: This URL changes when you move your installation folder of the reader)
            %s

            It is highly recommended to choose a period higher than 5 minutes because when you have much feeds added, the update requires more time than one period has. This will result in conflicts and server lags.

            A example cronjob (linux) that we recommend:',
    "admin.update.3" => "Update feeds manually",
    "admin.update.4" => "If you want to, you can update every single feed manually",
    "admin.update.5" => "Update all feeds",
    "admin.update.6" => "Update",
    "admin.update.7" => "Reader Application Update",
    "admin.update.8" => "You've downloaded a newer version of this reader? No problem, just copy all files from the new package over the existing application files.
            After you have done that you should click the following update button to update the database.",
    "admin.update.9" => "Update Database",
    "admin.update.10" => "Update successfully finished",
    "form.validation.error" => "Some data was not filled out properly",
    "admin.user.1" => "User Administration",
    "admin.user.2" => "Passwords did not match",
    "admin.user.3" => "Edit User",
    "admin.user.4" => "Create User",
    "admin.user.5" => "Role",
    "hello" => "Hi, %s",
    "feeds.1" => "Search for '%s'",
    "feeds.2" => "%s in %s",
    "browserscript.log" => "Importlog",
    "browserscript.close" => "You can close this window by executing the bookmark once again or you can refresh the page.",
    "browserscript.addfeeds" => "Add feeds from %s",
    "browserscript.addfeeds.btn" => "Add checked feeds",
    "browserscript.use" => "Usage - How can i use this feature?",
    "browserscript.info" => "nReeda gives you a powerfull tool to add feeds from other sites with just a few clicks. It will list all available feeds on a page and you can save it with one click to your nReeda account.",
    "browserscript.bookmark" => "Save the following URL in your favorites / bookmarks. If you be on a page that you want to subscribe you just need click on the saved bookmark.",
    "browserscript.nofeed" => "No feeds found on this page",
);