<?php

namespace Herrera\Phar\Update\Tests;

use Herrera\Phar\Update\Update;
use Herrera\PHPUnit\TestCase;
use Herrera\Version\Parser;
use Herrera\Version\Version;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use Phar;

class UpdateTest extends TestCase
{
    /** @var Update */
    private $update;

    /** @var Version */
    private $version;

    public function createPhar()
    {
        unlink($file = $this->createFile('test.phar'));

        $key = $this->getKey();
        $phar = new Phar($file);
        $phar->addFromString('test.php', '<?php echo "Hello, world!\n";');
        $phar->setStub($phar->createDefaultStub('test.php'));
        $phar->setSignatureAlgorithm(
            Phar::OPENSSL,
            $key[0]
        );

        file_put_contents($file . '.pubkey', $key[1]);

        unset($phar);

        $this->setPropertyValue($this->update, 'file', $file);

        return $file;
    }

    public function getKey()
    {
        return array(
            <<<PRIVATE
-----BEGIN PRIVATE KEY-----
MIIEuwIBADANBgkqhkiG9w0BAQEFAASCBKUwggShAgEAAoIBAQDKrDslvzuA1RC4
ND9B4X9/Gs9zl/eVWmrjc0B/dVo/0UslW9Hd2ubIuS9Y0UpOYdRhrpul6vY0DrGS
zIwVzwlB1wuM+UhVRuvtUQ9W/ljL0wlIMNVqOv0ZVDF5a3ok7dG643l4F7X5E95i
kEUfv7GGYmNhgIPpMCN901OCjlnkGGZxZ6sXSZtA1jCx7lMMw1vQc7d7bUBHI6uG
jSyPgMK4YtOMEs0kISMntaxevaa+26liaVMNLLYkihAJWL+3NfJEoWDuREJJI/DF
pDLC/RPyxhW0N+gIXbPq1pRe+N+pYVd5baI++6tNk6kRXCLQ6LPEEe+zOdeyTTit
vt6UjO5jAgMBAAECggEANNETjO+8GwPrmoWLIqkYZ9Bd1br5u4NXrbSgT1cO0OjD
E5ZNJ+rfD9oqu5O3MJwQE/DEAUYtKT3XCvGhZCGTQQRAr1lbf1W/MBZa0AnyrBNw
LM8FHu0Gfm5Rgln+99a+PF0Bj8lmE+YYo0kDqpVzNxk22vb56XAxH55N+g0M4gL+
6lHB0eMu+PVWXHOkE6lw2j8DOgHD8cUJNl8U3KH+0sy31cuFrwh9W7Chctt42ZKw
GEOiEgb6dxKjWhpSSfSz/ebr+Aage9odkGFhJs6N4bGrszX7q/gbwG5vDGJ+P2vY
DMG8qVNGqyT0jo5k2kRVE7IdnI2XViQ+lJgjefW6kQKBgQDxjdQHZYjLEb4uigQV
aUwxx3j5WpLkDqZBGGtPRmUnGxlDNZb1qpvNP0fu0MEYfkzfZnzctUC+8R9cOJda
+hquPq2UYOyJq7KhxrQoZC0ig3jFcxB4S6bpk1ueyeeoXLsn4ji2Vsy4Uw6ih+r2
cHhG+9pfcq6dVyTdiZcSd44g2QKBgQDWyyJhEqSivBPG1PrbGF+Mbb0ZowyLcYn6
GGPMWiM+2XnKGTAMnjJdWtdWSXUO1/SOMWi7gjDCLvLQCz+BqeP4sZCK7inr7kUs
mm+metEDEsP95+Ig2ZJLw908cFXujuo29NE458eVfB8iNtZncGozA2+1U0b74p78
BbtN/jCDmwKBgCgswpsIVBwSM2NiKRO2k6mj14cBfXTYyuYAvbhNqP08EJOREi6B
1a/pWnlp1vPP7dEqJpI+wyn+yIx6DRJgjpd0bUJEbJLpL6igd85P+wHGhAuy+4ZG
bthiXdanFhR2d9pGUdBh12LAzapSmM2sHxUPRl6hoFEi8Uq3W50CrWzhAoGBAIlJ
DJA++juJOpq7RhsDWQ7IlTTtofb+etH/BMp4Uk65cb5amvt1oXtJtJjSGp+CKC06
J1axv7hdiZSvm8ekbrFlzJz/3IuPn2cCzpn5pd3xAJQowb99UKRca+tVYZc4gTre
/1r/yfEhhES6CA/VKguxBpU+xP/5uOQcRbtz3E5BAn8LbSLhQJIrLvS8sK92K1ks
N+P4dTScMtrixTm8tlFltYIRF5xL0ZIw+cVhATONQhmoiYAo7bJkTwMQKPLxvH1o
BZRHaMuw5WgSe94fu+NAc//Y0J1mYUWZ1Z37RPcV3wYGc9mLTdmcYXNw0B+WhawB
jdLrFjBKzC47CipRZx1m
-----END PRIVATE KEY-----
PRIVATE
            ,
            <<<PUBLIC
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAyqw7Jb87gNUQuDQ/QeF/
fxrPc5f3lVpq43NAf3VaP9FLJVvR3drmyLkvWNFKTmHUYa6bper2NA6xksyMFc8J
QdcLjPlIVUbr7VEPVv5Yy9MJSDDVajr9GVQxeWt6JO3RuuN5eBe1+RPeYpBFH7+x
hmJjYYCD6TAjfdNTgo5Z5BhmcWerF0mbQNYwse5TDMNb0HO3e21ARyOrho0sj4DC
uGLTjBLNJCEjJ7WsXr2mvtupYmlTDSy2JIoQCVi/tzXyRKFg7kRCSSPwxaQywv0T
8sYVtDfoCF2z6taUXvjfqWFXeW2iPvurTZOpEVwi0OizxBHvsznXsk04rb7elIzu
YwIDAQAB
-----END PUBLIC KEY-----
PUBLIC
        );
    }

    public function testCopyTo()
    {
        $file = $this->createPhar();
        $target = $this->createFile('taco.phar');

        chmod($file, 0755);

        $this->update->copyTo($target);

        $this->assertFileEquals($file, $target);
        $this->assertFileEquals($file . '.pubkey', $target . '.pubkey');

        if (false === strpos(strtolower(PHP_OS), 'win')) {
            $this->assertEquals(0755, fileperms($file) & 511);
        }
    }

    public function testCopyToNotDownloaded()
    {
        $this->setExpectedException(
            'Herrera\\Phar\\Update\\Exception\\LogicException',
            'The update file has not been downloaded.'
        );

        $this->update->copyTo($this->createFile());
    }

    public function testCopyToCopyError()
    {
        $this->createPhar();

        $root = vfsStream::newDirectory('test');
        $root->addChild(vfsStream::newFile('test.phar', 0000));

        $this->setExpectedException(
            'Herrera\\Phar\\Update\\Exception\\FileException',
            'failed to open stream'
        );

        $this->update->copyTo('vfs://test/test.phar');
    }

    public function testDeleteFile()
    {
        $file = $this->createFile('test.phar');

        $this->setPropertyValue($this->update, 'file', $file);

        $this->update->deleteFile();

        $this->assertFileNotExists(dirname($file));
    }

    public function testDeleteFileUnlinkError()
    {
        $root = vfsStream::newDirectory('test');
        $root->addChild(vfsStream::newFile('test.phar', 0000));

        vfsStreamWrapper::setRoot($root);

        $this->setPropertyValue($this->update, 'file', 'vfs://test/test.phar');

        // unlink() does not issue warning on streams, but does return false
        $this->setExpectedException(
            'Herrera\\Phar\\Update\\Exception\\FileException'
        );

        $this->update->deleteFile();
    }

    public function testDeleteFileRmdirError()
    {
        $file = $this->createFile();

        $this->setPropertyValue(
            $this->update,
            'file',
            $file . DIRECTORY_SEPARATOR . 'test.phar'
        );

        $this->setExpectedException(
            'Herrera\\Phar\\Update\\Exception\\FileException',
            'rmdir'
        );

        $this->update->deleteFile();
    }

    public function testGetFile()
    {
        unlink($file = $this->createFile('test.phar'));

        $key = $this->getKey();
        $phar = new Phar($file);
        $phar->addFromString('test.php', '<?php echo "Hello, world!\n";');
        $phar->setStub($phar->createDefaultStub('test.php'));
        $phar->setSignatureAlgorithm(
            Phar::OPENSSL,
            $key[0]
        );

        file_put_contents($file . '.pubkey', $key[1]);

        unset($phar);

        $this->setPropertyValue($this->update, 'publicKey', $file . '.pubkey');
        $this->setPropertyValue($this->update, 'sha1', sha1_file($file));
        $this->setPropertyValue($this->update, 'url', $file);

        $this->assertFileEquals($file, $this->update->getFile());
    }

    public function testGetFileCorrupt()
    {
        $file = $this->createFile('test.phar');

        file_put_contents($file, '<?php echo "Hello, world!\n";');

        $this->setPropertyValue($this->update, 'publicKey', null);
        $this->setPropertyValue($this->update, 'sha1', sha1_file($file));
        $this->setPropertyValue($this->update, 'url', $file);

        $this->setExpectedException('UnexpectedValueException');

        $this->assertFileEquals($file, $this->update->getFile());
    }

    public function testGetFileSha1Mismatch()
    {
        $file = $this->createFile();

        file_put_contents($file, 'test');

        $this->setPropertyValue($this->update, 'publicKey', null);
        $this->setPropertyValue($this->update, 'url', $file);

        $this->setExpectedException(
            'Herrera\\Phar\\Update\\Exception\\FileException',
            'Mismatch of the SHA1 checksum (1234567890123456789012345678901234567890) of the downloaded file (' . sha1_file($file) . ').'
        );

        $this->update->getFile();
    }

    public function testGetName()
    {
        $this->assertEquals('test.phar', $this->update->getName());
    }

    public function testGetPublicKey()
    {
        $this->assertEquals(
            'http://example.com/test-1.2.3.phar.pubkey',
            $this->update->getPublicKey()
        );
    }

    public function testGetSha1()
    {
        $this->assertEquals(
            '1234567890123456789012345678901234567890',
            $this->update->getSha1()
        );
    }

    public function testGetUrl()
    {
        $this->assertEquals(
            'http://example.com/test.phar',
            $this->update->getUrl()
        );
    }

    public function testGetVersion()
    {
        $this->assertSame($this->version, $this->update->getVersion());
    }

    public function testIsNewer()
    {
        $this->assertTrue($this->update->isNewer(Parser::toVersion('1.0.0')));
    }

    protected function setUp()
    {
        $this->update = new Update(
            'test.phar',
            '1234567890123456789012345678901234567890',
            'http://example.com/test.phar',
            $this->version = Parser::toVersion('1.2.3'),
            'http://example.com/test-1.2.3.phar.pubkey'
        );
    }
}
