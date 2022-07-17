<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\Rest\Events\V1\Sink;

use Twilio\Exceptions\TwilioException;
use Twilio\ListResource;
use Twilio\Values;
use Twilio\Version;

/**
 * PLEASE NOTE that this class contains preview products that are subject to change. Use them with caution. If you currently do not have developer preview access, please contact help@twilio.com.
 */
class SinkTestList extends ListResource {
    /**
     * Construct the SinkTestList
     *
     * @param Version $version Version that contains the resource
     * @param string $sid The sid
     */
    public function __construct(Version $version, string $sid) {
        parent::__construct($version);

        // Path Solution
        $this->solution = ['sid' => $sid, ];

        $this->uri = '/Sinks/' . \rawurlencode($sid) . '/Test';
    }

    /**
     * Create the SinkTestInstance
     *
     * @return SinkTestInstance Created SinkTestInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function create(): SinkTestInstance {
        $payload = $this->version->create('POST', $this->uri);

        return new SinkTestInstance($this->version, $payload, $this->solution['sid']);
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string {
        return '[Twilio.Events.V1.SinkTestList]';
    }
}