<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ZfrRestTest\Router\Http;

use Metadata\MetadataFactory;
use PHPUnit_Framework_TestCase;
use ZfrRest\Router\Http\Matcher\BaseSubPathMatcher;
use ZfrRest\Router\Http\ResourceGraphRoute;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group  Coverage
 * @covers \ZfrRest\Router\Http\ResourceGraphRoute
 */
class ResourceGraphRouteTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $pluginManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $baseSubPathMatcher;

    public function setUp()
    {
        $this->metadataFactory    = $this->getMock('Metadata\MetadataFactory', [], [], '', false);
        $this->pluginManager      = $this->getMock('ZfrRest\ObjectRepository\ObjectRepositoryPluginManager', [], [], '', false);
        $this->baseSubPathMatcher = $this->getMock('ZfrRest\Router\Http\Matcher\BaseSubPathMatcher', [], [], '', false);
    }

    public function testReturnNullIfNotAHttpRequest()
    {
        $resourceGraphRoute = new ResourceGraphRoute(
            $this->metadataFactory,
            $this->pluginManager,
            $this->baseSubPathMatcher,
            new \stdClass(),
            '/route'
        );

        $this->assertNull($resourceGraphRoute->match($this->getMock('Zend\Stdlib\RequestInterface')));
    }

    public function testCanAssembleWithoutResource()
    {
        $resourceGraphRoute = new ResourceGraphRoute(
            $this->metadataFactory,
            $this->pluginManager,
            $this->baseSubPathMatcher,
            new \stdClass(),
            '/route'
        );

        $this->assertEquals('/route', $resourceGraphRoute->assemble());
    }

    public function testCanAssembleWithResource()
    {
        $resourceGraphRoute = new ResourceGraphRoute(
            $this->metadataFactory,
            $this->pluginManager,
            $this->baseSubPathMatcher,
            new \stdClass(),
            '/route'
        );

        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata->expects($this->once())
                      ->method('getIdentifierValues')
                      ->will($this->returnValue(['id' => 2]));

        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $metadata->expects($this->once())->method('getClassMetadata')->will($this->returnValue($classMetadata));

        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));

        $this->assertEquals('/route/2', $resourceGraphRoute->assemble(['resource' => $resource]));
    }

    public function testCanAssembleWithResourceAndAssociation()
    {
        $resourceGraphRoute = new ResourceGraphRoute(
            $this->metadataFactory,
            $this->pluginManager,
            $this->baseSubPathMatcher,
            new \stdClass(),
            '/route'
        );

        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata->expects($this->once())
                      ->method('getIdentifierValues')
                      ->will($this->returnValue(['id' => 2]));

        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $metadata->expects($this->once())->method('getClassMetadata')->will($this->returnValue($classMetadata));

        $metadata->expects($this->once())
                 ->method('hasAssociationMetadata')
                 ->with('tweets')
                 ->will($this->returnValue(true));

        $metadata->expects($this->once())
                 ->method('getAssociationMetadata')
                 ->with('tweets')
                 ->will($this->returnValue(['path' => 'tweets']));

        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));

        $this->assertEquals('/route/2/tweets', $resourceGraphRoute->assemble([
            'resource'    => $resource,
            'association' => 'tweets'
        ]));
    }
}
