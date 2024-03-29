# positive-gmail-check
check GMail for unread messages, with certainty

See installation prereqs and cautions further below.

This system requests Google OAUTH / OAUTH2 authority to access your GMail email metadata.  The purpose is to show a count of your unread emails.  I created 
this because, at the time, in late 2017 with Android 4.x, I could not trust a "pull" of email to show new messages.  Often I would pull and pull for hours
and not see messages on the GMail app.  I would have to use GMail.com for a reliable reading.  Given that pulling didn't work, "push" certainly didn't work 
for hours.

To this day (mid 2020 and Android 8.x), when a pull or push will show new emails is iffy, although the situation is much improved.   I've used this several 
to many times a day for 2.5 years (end of 2017 to mid 2020).  It works very near perfectly.  

I never really intended for anyone else to use it on my server.  On one hand, you're welcome to.  On the other hand, I make no guarantees about 
it.  You're also free to run your own copy.

With that said, it is running at https://kwynn.com/t/7/12/email/  .  It will immediately take you to an OAUTH screen.
*****
Updates - 2022/12/21

In this file, there were a couple of notes I'm deleting.  

Today I'm going to work on abstracting OAUTH2 from the specific GMail usage, or further abstracting.

I was using "FileToMongo" code.  I think I'm going to get rid of that, too, and just use files outside of the DOCUMENT_ROOT.  That is, 
I'm changing how I store the input and output secrets--input and output relative to in and out of Big Evil Goo(gle).  I have to do this for a 
client.  Otherwise, I might think about getting away from Goo finally.

further update 2022/12/22

A few hours ago I ran into Google' OAUTH's OOB (out of band) error.  This translates to using oauth.py or otherwise authorizing "by hand."  

I modified this code for a client so that I could get access to the live token file.  The first secret file is in fact read-only.  That one 
does not contain any auth tokens.  The one that I'm writing contains the live tokens.
******
Remember to explain the .htaccess - 2022/12
****
INSTALLATION STUFF

**
Be careful what file you set the debugger on.  I think it needs to be set on index.  
Setting it on main.php can lead to an infinite redirect loop
Update: I'm now less sure that was part of my problem, but I'll leave the caution there.
**
PREREQS

* my library
* composer stuff
* redirect / OAUTH2 URL stuff
* firewall stuff

*****
MY LIB

/opt/kwynn is a clone of https://github.com/kwynncom/kwynn-php-general-utils
*********

COMPOSER

I am using /opt/composer for my composer library, and /opt/kwynn assumes that.

You need Google libraries and MongoDB libraries, and MongoDB for that matter.  I'm unfortunately going to skip the MongoDB stuff here.
Because I had to update Google, here it is:

The composer commands below in turn create apiclient-services.

composer require google/auth
composer require google/apiclient

So, when I'm done:
ls /opt/composer/vendor/google
apiclient  apiclient-services  auth

***********
OAUTH2 / redirect URL

The redirect / OAUTH2 url needs to be registered with the Google Cloud Console and locally.  Generally, locally would be the 
file that has your client_secret and such.  I put that in MongoDB, though.

In the Google Cloud Console, the URL is part of the credentials screen for the specific app credentials.  (An app can have multiple credentials.)

In the qemail database:

db.getCollection('kwtom_files').find({})

There should only be one entry.

Under file / web / redirect_urls, the url must be there 
If you have multiple URLs, they must be there AND IN THE SAME ORDER AS IN THE GOOGLE CLOUD CONSOLE!
Otherwise you get:
{"error":"redirect_uri_mismatch","error_description":"Bad Request"}Thu, 27 Jan 2022 01:52:04 -0500

You'll get an "invalid grant" if you try to use an old auth code, where old is a matter of a few dozen ms.

***********
FIREWALL

If you're on your testing machine, the redirect URL must be open to the web.
The domain must route too, of course.

sudo ufw allow https

You might want http, too, to test the IP before you set to domain:

sudo ufw allow http

If you're dealing with an AT&T or perhaps other home router, see my 2022/01/12 blog entry, especially regarding IPv6:

https://kwynn.com/t/7/11/blog.html

**********

HISTORY

2022/01/27 02:16 - I just created a debugging branch.  The README file for that is useful.  I will remerge at least the README some time or another.
    UPDATE: I think this is the merged README, or close enough.  
