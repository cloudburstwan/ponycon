import { existsSync } from 'fs';

export default class Event {
    public id: string;
    public hidden: boolean;
    public type: EventType;
    public name: string = null;
    public summary: string = null;
    public environment: EventEnvironment = EventEnvironment.None;
    public location: EventLocation = { name: null, openstreetmap: null, googlemaps: null, applemaps: null };
    public dates: EventDates = { start: null, end: null};
    public showTimes: boolean = false;
    public breaks: EventBreak[] = [];
    public socials: EventSocials = {};
    public website: string = null;
    public streaming: EventStream = { enabled: false, stream: null, ponyTown: EventPonytown.None };

    constructor(id: string, type: EventType) {
        this.id = id;
        this.type = type;
    }

    get icon() {
        if (existsSync("/app/assets/events/"+this.id+".png")) return "/assets/events/"+this.id+".png";
        return "/assets/img/placeholder.png";
    }

    get safeOutput(): Event {
        if (!this.showTimes) {
            this.dates.start.setHours(0, 0, 0, 0);
            this.dates.end.setHours(0, 0, 0, 0);
        }
        let diffStart = Math.round((this.dates.start.getTime() - new Date().getTime()) / 1000);
        let diffEnd = Math.round((this.dates.end.getTime() - new Date().getTime()) / 1000);

        if (!(diffStart <= 0 && diffEnd > 0)) {
            this.streaming = { enabled: false, stream: null, ponyTown: EventPonytown.None };
        }
        return this;
    }
}

export enum EventType {
    None,
    Convention,
    Concert,
    AlbumRelease
}

export enum EventEnvironment {
    None,
    Online,
    IRL,
    Both
}

export enum EventPonytown {
    None,
    Main,
    Blue,
    Green
}

export interface EventLocation {
    name: string,
    openstreetmap: {
        name: string,
        url: string
    },
    googlemaps: {
        name: string,
        url: string
    },
    applemaps: {
        name: string,
        url: string
    }
}

export interface EventDates {
    start: Date,
    end: Date
}

export interface EventBreak {
    start: Date,
    end: Date
}

export interface EventStream {
    enabled: boolean,
    stream: string | null,
    ponyTown: EventPonytown
}

export interface EventSocials {
    discord?: EventSocial,
    mastodon?: EventSocial,
    twitter?: EventSocial,
    youtube?: EventSocial,
    twitch?: EventSocial,
    facebook?: EventSocial
}

export interface EventSocial {
    url: string,
    live: boolean
}