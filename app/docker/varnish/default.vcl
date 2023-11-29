vcl 4.0;

import std;

backend default {
    .host = "nginx:80";
}

backend imgproxy {
    .host = "imgproxy:8080";
}

backend mercure {
    .host = "mercure:80";
}

backend frontend {
    .host = "frontend:3000";
}

#acl invalidators {
#    "localhost";
#    "172.17.0.0"/16;
#}

sub vcl_recv {
    if (req.url ~ "^/_image/") {
        set req.backend_hint = imgproxy;
        set req.url = regsub(req.url, "^/_image", "");
        return (hash);
    }

    if (req.url ~ "^/.well-known/mercure") {
        set req.backend_hint = mercure;
        return (pipe);
    }

    if (req.url ~ "^/_tailwind/") {
        set req.backend_hint = frontend;
        return (pipe);
    }

    if (req.http.upgrade ~ "(?i)websocket") {
        return (pipe);
    }

    if (req.method == "BAN") {
        #if (!client.ip ~ invalidators) {
        #    return (synth(405, "Not allowed"));
        #}

        if (req.http.X-Cache-Tags) {
            ban("obj.http.X-Host ~ " + req.http.X-Host
                + " && obj.http.X-Url ~ " + req.http.X-Url
                + " && obj.http.content-type ~ " + req.http.X-Content-Type
                + " && obj.http.X-Cache-Tags ~ " + req.http.X-Cache-Tags
            );
        } else {
            ban("obj.http.X-Host ~ " + req.http.X-Host
                + " && obj.http.X-Url ~ " + req.http.X-Url
                + " && obj.http.content-type ~ " + req.http.X-Content-Type
            );
        }

        return (synth(200, "Banned"));
    }

    if (req.method != "GET" || req.url !~ "^/api") {
        return (pass);
    }

    set req.hash_ignore_busy = true;
    return (hash);
}

sub vcl_pipe {
    if (req.http.upgrade) {
        set bereq.http.upgrade = req.http.upgrade;
        set bereq.http.connection = req.http.connection;
    }
}


sub vcl_backend_response {
    #if ( beresp.status == 404 ) {
    #    set beresp.uncacheable = true;
    #    return (deliver);
    #}

    set beresp.http.X-Url = bereq.url;
    set beresp.http.X-Host = bereq.http.host;
}

sub vcl_deliver {
    unset resp.http.X-Url;
    unset resp.http.X-Host;

    # Unset the tagged cache headers
    unset resp.http.X-Cache-Tags;

    unset resp.http.Server;
    unset resp.http.Via;
    unset resp.http.X-Varnish;

    if (obj.hits > 0) {
        set resp.http.X-Cache = "HIT";
    } else {
        set resp.http.X-Cache = "MISS";
    }
}
