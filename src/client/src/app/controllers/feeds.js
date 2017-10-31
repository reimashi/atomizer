class FeedController {
    constructor($scope, $location, $timeout, authManager, apiService) {
        if (!authManager.isAuthenticated()) {
            $location.path("/");
        }

        this.$scope = $scope;
        this.$timeout = $timeout;
        this.api = apiService;

        // Scope functions
        $scope.addFeed = () => this.addFeed();
        $scope.openFeed = (id) => this.openFeed(id);
        $scope.discard = (id) => this.discard(id);
        $scope.readLater = (id) => this.readLater(id);
    }

    $onInit() {
        // Variable definitions
        this.$scope.notEdit = false;
        this.$scope.newFeedUrl = "";
        this.$scope.feedFilter = -1;
        this.$scope.feeds = [];
        this.feedArticles = [];
        this.$scope.feedsFiltered = [];

        this.updateFeeds().then(() => { this.updateFeeds(true); });

        let self = this;
        this.$scope.$watch("feedFilter", (newValue, oldValue) => {
            if (newValue === oldValue) { return; }
            self.filterFeeds(newValue);
        });
    }

    /**
     * Update feeds from server and parse data
     * @param update True to force database update in server side
     * @returns {Promise}
     */
    updateFeeds(update) {
        let self = this;
        return new Promise((accept, reject) => {
            self.$scope.notUpdate = true;
            this.api.getFeeds(Boolean(update))
                .then((data) => {
                    let tmpFeeds = [];

                    for (let fid in data) {
                        tmpFeeds.push({
                            id: data[fid].id,
                            name: data[fid].title,
                        });
                    }

                    let tmpArticles = [];

                    for (let fid in data) {
                        for (let atid in data[fid].items) {

                            tmpArticles.push({
                                id: data[fid].items[atid].id,
                                feed: data[fid].id,
                                url: data[fid].items[atid].url,
                                name: data[fid].title,
                                title: data[fid].items[atid].title,
                                description: data[fid].items[atid].summary,
                                updated: data[fid].items[atid].updated,
                                read_later: data[fid].items[atid].read_later,
                                readed: data[fid].items[atid].readed,
                            });
                        }
                    }

                    self.$timeout(function() {
                        self.$scope.$apply(() => {
                            self.$scope.feeds = tmpFeeds;
                            self.feedArticles = tmpArticles;
                            self.filterFeeds(self.$scope.feedFilter);
                        });
                    });

                    self.$scope.notUpdate = false;
                    accept();
                })
                .catch((err) => {
                    console.error(err);
                    self.$scope.notUpdate = false;
                    reject(err);
                });
        });
    }

    /**
     * Filter the retrieved feeds with form configurations
     * @param id ID of the feed to filter
     */
    filterFeeds(id) {
        let self = this;
        let feedId = Number(id);

        if (feedId !== -1) {
            let tmpFiltered = [];
            for (let ind in self.feedArticles) {
                if (self.feedArticles[ind].feed === feedId) {
                    tmpFiltered.push(self.feedArticles[ind]);
                }
            }

            self.$timeout(function() {
                self.$scope.$apply(() => {
                    self.$scope.feedsFiltered = tmpFiltered;
                });
            });
        }
        else {
            self.$timeout(function() {
                self.$scope.$apply(() => {
                    self.$scope.feedsFiltered = self.feedArticles;
                });
            });
        }
    }

    /**
     * Add feed to the user database and force update
     */
    addFeed() {
        if (this.$scope.newFeedUrl && this.$scope.newFeedUrl.length > 0) {
            let url = String(this.$scope.newFeedUrl);

            this.api.addFeed(url)
                .then(() => {
                    this.updateFeeds(true);
                })
                .catch((err) => {
                    console.error(err);
                })
        }
    }

    /**
     * Get the feed by id
     * @param id
     * @returns feed or null
     */
    getFeed(id) {
        for(let article in this.feedArticles) {
            if (this.feedArticles[article].id === id)
                return this.feedArticles[article];
        }
        return null;
    }

    /**
     * Open a item/article in new tab an tag as readed
     * @param id Item id
     */
    openFeed(id) {
        let article = this.getFeed(id);

        if (article !== null) {
            let win = window.open(article.url, '_blank');
            win.focus();

            this.discard(id);
        }
    }

    /**
     * Tag a item/article as readed
     * @param id Item id
     */
    discard(id) {
        for(let article in this.feedArticles) {
            if (this.feedArticles[article].id === id) {
                // Tag local
                this.feedArticles[article].readed = true;

                // Tag remote
                this.api.tagFeedReaded(this.feedArticles[article].feed, id)
                    .catch((err) => console.error(err));

                break;
            }
        }
    }

    /**
     * Tag a item/article to read later
     * @param id Item id
     */
    readLater(id) {
        for(let article in this.feedArticles) {
            if (this.feedArticles[article].id === id) {
                // Tag local
                this.feedArticles[article].read_later = true;

                // Tag remote
                this.api.tagFeedReadLater(this.feedArticles[article].feed, id)
                    .catch((err) => console.error(err));

                break;
            }
        }
    }
}

export { FeedController };