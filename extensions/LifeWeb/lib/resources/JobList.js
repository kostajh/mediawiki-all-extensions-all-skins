/**
 * Queued job list
 *
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

var LW = LW || {};
LW.Job = function ( desc, run ) {
	if ( !( this instanceof LW.Job ) ) { return new LW.Job( desc, run ); }

	this.run = run || function () {};
	this.desc = desc || 'Empty job';

	return this;
};

LW.JobList = function ( desc ) {
	if ( !( this instanceof LW.JobList ) ) { return new LW.JobList( desc ); }

	if ( !desc instanceof String ) {
		throw Error( 'First argument must be {String} description' );
	}

	this.verbose = false;
	this.detach = false;
	this.jobs = [];

	// / @type {LWUI.DynamicList}
	this.dom = new LWUI.DynamicList( {
		text: desc
	} );

	this.promise = jQuery.Deferred();

	this.count = {
		jobs: 0,

		always: 0,
		done: 0,
		fail: 0,

		job: 0
	};

	this.tStart = undefined;

	return this;
};
LW.JobList.prototype.add = function ( job ) {
	this.jobs.push( job );
};
/**
 * @private
 * @param {Job|undefined} fn
 */
LW.JobList.prototype.createJob = function ( fn ) {
	if ( fn === undefined ) { return undefined; }

	return ( function ( job, jobList ) {
		return function () {

			var $item = jobList.dom.add( job.desc );

			if ( jobList.verbose ) {
				console.log( 'Starting job: ' + job.desc );
			}

			job.run().done( function () {

				jobList.count.done++;
				jobList.next();
				$item.detach();

			} ).fail( function () {

				jobList.count.fail++;
				$item.text( $item.text() + ' – Failed' );

			} ).always( function () {
				jobList.count.always++;
			} );
		};
	}( fn, this ) );
};
LW.JobList.prototype.start = function start( N ) {

	this.add = function () { throw Error( 'Job list has already been started, cannot add new jobs.' ); };

	this.jobs = this.jobs.reverse();

	this.tStart = new Date();
	this.count.jobs = this.jobs.length;
	for ( var n = 0; n < N; n++ ) {
		this.next();
	}

	this.promise.done( function ( list ) {
		return function () {

			var tEnd = new Date(),
				millis = tEnd.getTime() - list.tStart.getTime();

			list.dom.add( 'Completed ' + list.count.done + ' jobs in ' + ( millis / 1000 ) + ' seconds.' );

			if ( list.detach || list.count.always === 0 ) {
				list.dom.$dom.detach();
			}

		};
	}( this ) );

	return this.promise;
};
LW.JobList.prototype.next = function () {

	var job = this.createJob( this.jobs.pop() );
	if ( job === undefined ) {
		if ( this.count.done === this.count.jobs ) {

			// All jobs done.
			this.promise.resolve( {
				completed: this.count.done
			} );
		}
	} else {
		job();
	}

};
LW.JobList.prototype.detachOnDone = function ( detach ) {
	this.detach = detach;
};
