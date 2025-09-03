import {readFileSync, writeFileSync, watchFile} from "fs";
import xml from "xml-js";
import Event, {EventEnvironment, EventPonytown, EventType} from "../Types/Event";

export default class XML {
    private supportedSocials: string[] = [
        "discord",
        "mastodon",
        "twitter",
        "youtube",
        "twitch",
        "facebook"
    ];

    private ponytownEventServers = {
        main: EventPonytown.Main,
        green: EventPonytown.Green,
        blue: EventPonytown.Blue
    }

    public events: Event[];
    public rawEventData: any;

    constructor() {
        this._parse();
        this._removeOldEvents();

        watchFile("/app/data/events.xml", () => {
            this._parse();
        });

        setInterval(() => {
            this._removeOldEvents();
        }, (60 * 60 * 1000));
    }

    _removeOldEvents() {
        let newEvents: Event[] = [];

        this.events.forEach(event => {
            if (Date.parse(event.dates.end.toUTCString()) + (24 * 60 * 60 * 1000) > Date.now()) {
                // The event is not over yet.
                newEvents.push(event);
            } else {
                // Needs removing!

                this.rawEventData.elements[0].elements.splice(
                    this.rawEventData.elements[0].elements.findIndex(element => {
                        if (element.name !== "event") return false;
                        if (element.attributes === undefined) return false;

                        return element.attributes.id === event.id;
                    }), 1
                );
            }
        });

        //this.events = newEvents;
        let xmlString = xml.json2xml(JSON.stringify(this.rawEventData, null, 4), { compact: false, spaces: 4 });

        try {
            writeFileSync("/app/data/events.xml", xmlString);
        } catch (err: any) {
            console.warn("Encountered error while saving events.xml: " + err.message);
        }
    }

    _parse() {
        this.events = [];
        
        let xmlString = readFileSync("/app/data/events.xml").toString();

        let xmlData = JSON.parse(xml.xml2json(xmlString, {compact: false}));

        this.rawEventData = xmlData;

        if (xmlData.elements === null) return;
        if (xmlData.elements[0].name !== "events") return;
        if (xmlData.elements[0].elements === undefined) return;

        xmlData.elements[0].elements.forEach(element => {
            if (element.name !== "event") return;
            if (element.attributes === undefined) return;
            if (element.attributes.id === undefined) return;
            if (element.attributes.type === undefined) return;
            if (element.attributes.hidden === "true") return;
            if (element.attributes.showtimes === undefined) return;

            if (element.elements === undefined) return;

            let id = element.attributes.id;
            let showTimes = element.attributes.showtimes === "true";
            let type;

            switch (element.attributes.type) {
                case "convention":
                    type = EventType.Convention;
                    break;
                case "concert":
                    type = EventType.Concert;
                    break;
                case "album":
                    type = EventType.AlbumRelease;
                    break;
                default:
                    type = EventType.None;
                    break;
            }

            let event = new Event(id, type);
            event.showTimes = showTimes;

            element.elements.forEach(element2 => {
                switch (element2.name) {
                    case "name":
                        if (element2.elements === undefined) return;
                        if (element2.elements[0].text === undefined) return;
                        event.name = element2.elements[0].text.trim();
                        break;
                    case "summary":
                        if (element2.elements === undefined) return;
                        if (element2.elements[0].text === undefined) return;
                        event.summary = element2.elements[0].text.trim();
                        break;
                    case "online":
                        if (event.environment === EventEnvironment.IRL) {
                            event.environment = EventEnvironment.Both;
                        } else {
                            event.environment = EventEnvironment.Online;
                        }
                        break;
                    case "irl":
                        if (event.environment === EventEnvironment.Online) {
                            event.environment = EventEnvironment.Both;
                        } else {
                            event.environment = EventEnvironment.IRL;
                        }
                        break;
                    case "location":
                        if (element2.elements === undefined) return;

                        element2.elements.forEach(element3 => {
                            if(element3.elements === undefined) return;

                            switch(element3.name) {
                                case "name":
                                    if (element3.elements[0].text === undefined) return;
                                    event.location.name = element3.elements[0].text.trim();
                                    break;
                                case "openstreetmap":
                                    event.location.openstreetmap = {
                                        name: null,
                                        url: null
                                    }
                                    element3.elements.forEach(element4 => {
                                        if (element4.elements === undefined) return;
                                        if (element4.elements[0].text === undefined) return;

                                        switch(element4.name) {
                                            case "url":
                                                event.location.openstreetmap.url = element4.elements[0].text.trim();
                                                break;
                                            case "name":
                                                event.location.openstreetmap.name = element4.elements[0].text.trim();
                                                break;
                                            default:
                                                return;
                                        }
                                    });
                                    break;
                                case "googlemaps":
                                    event.location.googlemaps = {
                                        name: null,
                                        url: null
                                    }
                                    element3.elements.forEach(element4 => {
                                        if (element4.elements === undefined) return;
                                        if (element4.elements[0].text === undefined) return;

                                        switch(element4.name) {
                                            case "url":
                                                event.location.googlemaps.url = element4.elements[0].text.trim();
                                                break;
                                            case "name":
                                                event.location.googlemaps.name = element4.elements[0].text.trim();
                                                break;
                                            default:
                                                return;
                                        }
                                    });
                                    break;
                                case "applemaps":
                                    event.location.applemaps = {
                                        name: null,
                                        url: null
                                    }
                                    element3.elements.forEach(element4 => {
                                        if (element4.elements === undefined) return;
                                        if (element4.elements[0].text === undefined) return;

                                        switch(element4.name) {
                                            case "url":
                                                event.location.applemaps.url = element4.elements[0].text.trim();
                                                break;
                                            case "name":
                                                event.location.applemaps.name = element4.elements[0].text.trim();
                                                break;
                                            default:
                                                return;
                                        }
                                    });
                                    break;
                                default:
                                    return;
                            }
                        });
                        break;
                    case "start":
                        if (element2.elements === undefined) return;
                        if (element2.elements[0].text === undefined) return;
                        event.dates.start = new Date(element2.elements[0].text.trim());
                        break;
                    case "end":
                        if (element2.elements === undefined) return;
                        if (element2.elements[0].text === undefined) return;
                        event.dates.end = new Date(element2.elements[0].text.trim());
                        break;
                    case "break":
                        // Signifies that the event isn't active, but is still ongoing.
                        // Contains 2 elements: start and end.
                        // There can be multiple breaks.
                        let start: Date = null;
                        let end: Date = null;

                        element2.elements.forEach(breakElement => {
                            if(breakElement.elements === undefined) return;
                            if(breakElement.elements[0].text === undefined) return;

                            switch (breakElement.name) {
                                case "start":
                                    start = new Date(breakElement.elements[0].text.trim());
                                    break;
                                case "end":
                                    end = new Date(breakElement.elements[0].text.trim());
                                    break;
                                default:
                                    return;
                            }
                        });

                        if (start === null || end === null) return;

                        event.breaks.push({
                            start, end
                        });
                        break;
                    case "socials":
                        if (element2.elements === undefined) return;

                        element2.elements.forEach(social => {
                            if (social.elements === undefined) return;

                            if (!this.supportedSocials.includes(social.name)) return;

                            let url: string = null;
                            let live: boolean = null;

                            social.elements.forEach(item => {
                                if (item.elements === undefined) return;
                                if (item.elements[0].text === undefined) return;

                                switch (item.name) {
                                    case "url":
                                        url = item.elements[0].text.trim();
                                        break;
                                    case "live":
                                        live = item.elements[0].text === "true";
                                        break;
                                    default:
                                        return;
                                }
                            });

                            if (url === null || live === null) return;

                            event.socials[social.name] = {
                                url,
                                live
                            };
                        });

                        break;
                    case "website":
                        if (element2.elements === undefined) return;
                        if (element2.elements[0].text === undefined) return;
                        event.website = element2.elements[0].text.trim();
                        break;
                    case "streaming":
                        if (element2.attributes === null) return;

                        event.streaming.enabled = element2.attributes.enabled === "true";
                        
                        if (element2.elements !== undefined) {
                            element2.elements.forEach(element3 => {
                                if (element3.elements === undefined) return;
                                if (element3.elements[0].text === undefined) return;
                                
                                switch (element3.name) {
                                    case "stream":
                                        event.streaming.stream = element3.elements[0].text;
                                        break;
                                    case "ponyTown":
                                        if (!this.ponytownEventServers[element3.elements[0].text]) return;
                                        event.streaming.ponyTown = this.ponytownEventServers[element3.elements[0].text];
                                        break;
                                }
                            });
                        }
                        break;
                    default:
                        // just ignore it honestly
                        return;
                }
            });

            this.events.push(event);
        });
    }
}