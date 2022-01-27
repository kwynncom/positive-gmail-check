# positive-gmail-check
check GMail for unread messages, with certainty

See prereqs further below.

This system requests Google OAUTH / OAUTH2 authority to access your GMail email metadata.  The purpose is to show a count of your unread emails.  I created 
this because, at the time, in late 2017 with Android 4.x, I could not trust a "pull" of email to show new messages.  Often I would pull and pull for hours
and not see messages on the GMail app.  I would have to use GMail.com for a reliable reading.  Given that pulling didn't work, "push" certainly didn't work 
for hours.

To this day (mid 2020 and Android 8.x), when a pull or push will show new emails is iffy, although the situation is much improved.   I've used this several 
to many times a day for 2.5 years (end of 2017 to mid 2020).  It works very near perfectly.  

I never really intended for anyone else to use it on my server (see below).  On one hand, you're welcome to.  On the other hand, I make no guarantees about 
it.  You're free to run your own copy.

With that said, it is running at https://kwynn.com/t/7/12/email/  .  It will immediately take you to an OAUTH screen.

*****
Be careful what file you set the debugger on.  I think it needs to be set on index.  
Setting it on main.php can lead to an infinite redirect loop
****
PREREQS

1. composer stuff below
2. The redirect / OAUTH2 url needs to be registered with the Google Cloud and possibly in your local auth file.
    The URL is part of the credentials screen for the specific app credentials.  (An app can have multiple credentials.)
    Yes, it needs to be in the file, which in my case is in the datbase.  See below.
3. If you're on your testing machine, the URL must be open to the web.
    The domain must route too, of course.
    sudo ufw allow https
    # might want http, too, to test the IP before you set to domain
    sudo ufw allow http
    
    more below.

******


*** COMPOSER

I am using /opt/composer for my composer library.  The composer commands below in turn create apiclient-services.

composer require google/auth
composer require google/apiclient

So, when I'm done:
ls /opt/composer/vendor/google
apiclient  apiclient-services  auth
************
db.getCollection('kwtom_files').find({})
under File / web / redirect_urls, the url must be there
If you have multiple URLs, they must be there AND IN THE SAME ORDER AS IN THE GOOGLE CLOUD CONSOLE!
Otherwise you get:
{"error":"redirect_uri_mismatch","error_description":"Bad Request"}Thu, 27 Jan 2022 01:52:04 -0500

You'll get an "invalid grant" if you try to use an old auth code, where old is a matter of a few dozen ms.


*************
external test of port 80 / http not secure:

curl [2400:abcd::0]
