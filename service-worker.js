"use strict";

var name = "";

// The version of the cache, changing this will force everything to be cached
// again.
var version = "";

var files = [

];

self.addEventListener("install", function (event) {
	event.waitUntil(
		caches.open(`${name}-v${version}`).then(function (cache) {
			return cache.addAll(files);
		})
	);
});

self.addEventListener("activate", function (event) {
	event.waitUntil(
		caches.keys().then(function (keyList) {
			return Promise.all(keyList.map(function (key) {
				if (key !== `${name}-v${version}`) {
					return caches.delete(key);
				}
			}));
		})
	);

	return self.clients.claim();
});

self.addEventListener("fetch", function (event) {
	if (event.request.method !== "GET") {
		return;
	}

	event.respondWith(
		caches.match(event.request).then(function (cached) {
			var networked = fetch(event.request)
							.then(fetchedFromNetwork, unableToResolve)
							.catch(unableToResolve);
			return cached || networked;

			function fetchedFromNetwork (response) {
				var cacheCopy = response.clone();

				caches.open(`${name}-v${version}`).then(function add (cache) {
					cache.put(event.request, cacheCopy);
				});

				return response;
			}

			function unableToResolve () {
				return new Response("<h1>Service Unavailable</h1>", {
					status: 503,
					statusText: "Service Unavailable",
					headers: new Headers({
						"Content-Type": "text/html"
					})
				});
			}
		})
	);
});