<?php
// 获取请求的路径
$requestUri = $_SERVER['REQUEST_URI'];

// 数据库连接
$host = 'localhost';
$username = 'root';
$password = 'su15906477192';
$database = 'pinche'; // 替换为你的数据库名称

$conn = new mysqli($host, $username, $password, $database);


class WXBizDataCrypt
{
    private $appid;
    private $sessionKey;

    public function __construct($appid, $sessionKey)
    {
        $this->appid = $appid;
        $this->sessionKey = $sessionKey;
    }

    public function decryptData($encryptedData, $iv, &$data)
    {
        if (strlen($this->sessionKey) != 24) {
            return -41001;
        }
        $aesKey = base64_decode($this->sessionKey);

        if (strlen($iv) != 24) {
            return -41002;
        }
        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encryptedData);

        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj = json_decode($result);
        if ($dataObj == null) {
            return -41003;
        }
        if ($dataObj->watermark->appid != $this->appid) {
            return -41004;
        }

        $data = $result;
        return 0;
    }
}

$config = [
    'appid' => 'wxe378c1ebc7aeeff6',
    'secret' => 'e6daee5b266424478eaa9dc3129ae51c'
];

function getWechatSession($code, $appid, $secret)
{
    $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if (isset($result['errcode'])) {
        return [
            'success' => false,
            'error' => $result
        ];
    }
    
    return [
        'success' => true,
        'data' => [
            'openid' => $result['openid'],
            'session_key' => $result['session_key']
        ]
    ];
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];



// 检查数据库连接是否成功
if ($conn->connect_error) {
    http_response_code(500);
    $error = [
        'status' => 'error',
        'message' => '数据库连接失败: ' . $conn->connect_error
    ];
    header('Content-Type: application/json');
    echo json_encode($error);
    exit;
}

// 检查是否为 /mp/config 路径
if (strpos($requestUri, '/mp/config') !== false) {
    // 检查 type 参数是否存在
    if (isset($_GET['type'])) {
        $type = $_GET['type'];

        // 根据 type 参数返回对应的配置数据
        $configData = [
            "data" => [
                "SWITCH" => [
                    "SwitchAdd" => "1",
                    "SwitchCallPhone" => "1"
                ],
                "PAGE_SIZE" => [
                    "PageSizeIndex" => "6",
                    "PageSizeList" => "10"
                ],
                "SHARE_IMG" => [
                    "ShareImgIndex" => "http://localhost:90/e2.jpg",
                    "ShareImgDetail" => "http://localhost:90/e2.jpg"
                ],
                "SHARE_TEXT" => [
                    "ShareTextIndex" => "推荐同济拼车，快来试试~",
                    "ShareTextDetail" => "发布了新行程，快来看看吧~"
                ]
            ]
        ];

        // 设置响应头为 JSON 格式
        header('Content-Type: application/json');
        // 输出 JSON 数据
        echo json_encode($configData);
    } else {
        // 如果 type 参数不存在，返回错误信息
        http_response_code(400);
        $error = [
            'message' => '缺少 type 参数'
        ];
        header('Content-Type: application/json');
        echo json_encode($error);
    }
} 
// 检查是否为 /mp/user/auth 路径
else if (strpos($requestUri, '/mp/user/auth') !== false) {
    // 获取 POST 数据
    $postData = json_decode(file_get_contents('php://input'), true);

    $encryptedData = $postData['encryptedData'];
    $iv = $postData['iv'];
    $rawData = $postData['rawData'];
    $signature = $postData['signature'];
    $userInfo = json_encode($postData['userInfo']); // 将用户信息存储为 JSON 格式
    $token = $postData['token'];
    $code = $postData['code'];

    // $code = $_POST['code'];
    // $encryptedData = $_POST['encryptedData'];
    // $iv = $_POST['iv'];
    
    // 获取微信登录凭证
    $sessionResult = getWechatSession($code, $config['appid'], $config['secret']);
    
    if (!$sessionResult['success']) {
        die(json_encode([
            'code' => 50001,
            'msg' => '获取微信凭证失败',
            'error' => $sessionResult['error']
        ]));
    }
    
    $openid = $sessionResult['data']['openid'];
    $sessionKey = $sessionResult['data']['session_key'];
    
    // 解密用户信息
    $pc = new WXBizDataCrypt($config['appid'], $sessionKey);
    $errCode = $pc->decryptData($encryptedData, $iv, $data);
    
    if ($errCode == 0) {
        $userInfo = json_decode($data, true);
        
        // TODO: 处理用户信息（如存储到数据库）
        // ...
        
        echo json_encode([
            'code' => 200,
            'msg' => '获取用户信息成功',
            'data' => $userInfo,
            'pc' => $pc
        ]);
    } else {
        echo json_encode([
            'code' => $errCode,
            'msg' => '解密失败',
            'error' => '错误码: ' . $errCode
        ]);
    }
}


else if (strpos($requestUri, '/mp/user/release/count') !== false) {
    // 模拟返回发布的行程数量
    $response = [
        'status' => 'success',
        'message' => '获取发布数量成功',
        'data' => 5,
        'meta' => [
            'code' => 2000
        ]
    ];

    // 设置响应头为 JSON 格式
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
// else if (strpos($requestUri, '/mp/travel/list') !== false) {
else if (preg_match('/\/mp\/travel\/list\/(\d+)/', $requestUri, $matches)) {
    $type = $matches[1]; // 提取路径中的数字 (0, 1, 2)
    // 获取查询参数
    $queryParams = [];
    parse_str(parse_url($requestUri, PHP_URL_QUERY), $queryParams);

    $page = $queryParams['page'] ?? 1;
    $count = $queryParams['count'] ?? 6;
    $offset = ($page - 1) * $count;


    // 根据 $type 执行不同的逻辑
    if ($type == 0) {
        // 处理 /mp/travel/list/0 的逻辑
        $sql = "SELECT * FROM travels LIMIT $offset, $count";
    } else if ($type == 2) {
        // 处理 /mp/travel/list/1 的逻辑
        $sql = "SELECT * FROM travels WHERE type = 1 LIMIT $offset, $count";
    } else if ($type == 1) {
        // 处理 /mp/travel/list/2 的逻辑
        $sql = "SELECT * FROM travels WHERE type = 2 LIMIT $offset, $count";
    } else {
        // 如果 type 不在预期范围内，返回错误
        http_response_code(400);
        $response = [
            'status' => 'error',
            'message' => '无效的类型'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    // 查询行程数据
    $result = $conn->query($sql);

    $list = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $list[] = [
                'id' => $row['id'],
                'type' => $row['type'],
                'origin' => $row['origin'],
                'dest' => $row['dest'],
                'time' => $row['time'],
                'num' => $row['num'],
                'price' => $row['price'],
                'mobileNo' => $row['mobile_no'],
                'remarks' => $row['remarks']
            ];
        }
    }

    // 查询总行程数量
    $totalSql = "SELECT COUNT(*) as total FROM travels";
    $totalResult = $conn->query($totalSql);
    $totalNum = $totalResult->fetch_assoc()['total'] ?? 0;

    // 返回响应
    $response = [

        'status' => 'success',
        'message' => '获取行程列表成功',
        'data' => [
            'type' => $type,
            'list' => $list,
            'pageNum' => $page,
            'pageSize' => $count,
            'totalNum' => $totalNum
        ]
    ];

    // 设置响应头为 JSON 格式
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

else if (preg_match('/\/mp\/travel\/(\d+)/', $requestUri, $matches)) {
    $travelId = $matches[1]; // 获取路径中的 ID

    // 查询数据库中的行程详情
    $sql = "SELECT * FROM travels WHERE id = $travelId";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $travel = $result->fetch_assoc();

        // 返回行程详情
        $response = [
            'status' => 'success',
            'message' => '获取行程详情成功',
            'data' => [
                'id' => $travel['id'],
                'type' => $travel['type'],
                'origin' => $travel['origin'],
                'originLat' => $travel['origin_lat'],
                'originLng' => $travel['origin_lng'],
                'dest' => $travel['dest'],
                'destLat' => $travel['dest_lat'],
                'destLng' => $travel['dest_lng'],
                'time' => $travel['time'],
                'num' => $travel['num'],
                'price' => $travel['price'],
                'mobileNo' => $travel['mobile_no'],
                'returnTime' => $travel['return_time'],
                'via' => $travel['via'],
                'remarks' => $travel['remarks']
            ]
        ];
    } else {
        // 如果未找到行程，返回错误信息
        http_response_code(404);
        $response = [
            'status' => 'error',
            'message' => '未找到对应的行程'
        ];
    }

    // 设置响应头为 JSON 格式
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

else if (strpos($requestUri, '/mp/travel/add') !== false) {
    // 获取 POST 数据
    $postData = json_decode(file_get_contents('php://input'), true);

    // 验证必要字段是否存在
    if (!isset($postData['type'], $postData['origin'], $postData['dest'], $postData['time'], $postData['num'], $postData['price'], $postData['mobileNo'])) {
        http_response_code(400);
        $response = [
            'status' => 'error',
            'message' => '缺少必要的参数'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // 提取字段值
    $type = $postData['type'];
    $origin = $postData['origin'];
    $originLat = $postData['originLat'];
    $originLng = $postData['originLng'];
    $dest = $postData['dest'];
    $destLat = $postData['destLat'];
    $destLng = $postData['destLng'];
    $time = date('Y-m-d H:i:s', strtotime($postData['time']));
    $num = $postData['num'];
    $price = $postData['price'];
    $mobileNo = $postData['mobileNo'];
    $returnTime = isset($postData['returnTime']) ? date('Y-m-d H:i:s', strtotime($postData['returnTime'])) : null;
    $via = $postData['via'] ?? null;
    $remarks = $postData['remarks'] ?? null;


    // 插入行程数据到数据库
    $stmt = $conn->prepare("INSERT INTO travels (type, origin, origin_lat, origin_lng, dest, dest_lat, dest_lng, time, num, price, mobile_no, return_time, via, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "issssddsidssss",
        $type,
        $origin,
        $originLat,
        $originLng,
        $dest,
        $destLat,
        $destLng,
        $time,
        $num,
        $price,
        $mobileNo,
        $returnTime,
        $via,
        $remarks
    );

    if ($stmt->execute()) {
        // 返回成功响应
        $response = [
            'status' => 'success',
            'message' => '行程添加成功',
            'data' => [
                'id' => $stmt->insert_id
            ]
        ];
    } else {
        // 返回错误响应
        http_response_code(500);
        $response = [
            'status' => 'error',
            'message' => '行程添加失败: ' . $stmt->error
        ];
    }

    // 设置响应头为 JSON 格式
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

else if (strpos($requestUri, '/mp/cites') !== false) {
    // 获取查询参数 city
    $city = $_GET['city'] ?? '';

    if (empty($city)) {
        http_response_code(400);
        $response = [
            'status' => 'error',
            'message' => '缺少 city 参数'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // 查询数据库中的城市信息
    $sql = "SELECT * FROM cities WHERE name LIKE '%$city%'";
    $result = $conn->query($sql);

    $cities = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cities[] = [
                'id' => $row['id'],
                'selectCity' => $row['name'],
                'province' => $row['province']
            ];
        }
    }

    if (empty($cities)) {
        http_response_code(404);
        $response = [
            'status' => 'error',
            'message' => '未找到相关城市信息'
        ];
    } else {
        $response = [
            'status' => 'success',
            'message' => '获取城市信息成功',
            'data' => $cities
            // 'selectCity' => $cities[0]['name']
        ];
    }

    // 设置响应头为 JSON 格式
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
else if (strpos($requestUri, '/mp/user/upload/avatar') !== false) {
    // 获取 JSON 数据
    $postData = json_decode(file_get_contents('php://input'), true);
    $base64Data = $postData['avatar'];
    
    // 验证数据
    if (empty($base64Data)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '头像数据不能为空'
        ]);
        exit;
    }

    // 处理图片保存
    $uploadDir = 'uploads/avatars/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = uniqid() . '.png';
    $filepath = $uploadDir . $filename;

    try {
        $imageData = base64_decode($base64Data);
        if (file_put_contents($filepath, $imageData)) {
            $baseUrl = 'http://localhost:90/'; // 替换为你的服务器地址
            $avatarUrl = $baseUrl . $filepath;
            
            echo json_encode([
                'success' => true,
                'url' => $avatarUrl,
                'message' => '头像上传成功'
            ]);
        } else {
            throw new Exception('文件保存失败');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => '头像上传失败: ' . $e->getMessage()
        ]);
    }
    exit;
}
// 处理用户注册
else if (strpos($requestUri, '/mp/user/register') !== false) {
    // 获取POST数据
    $postData = json_decode(file_get_contents('php://input'), true);
    
    // 验证必填字段
    if (!isset($postData['nickName']) || !isset($postData['phone']) || 
        !isset($postData['password']) || !isset($postData['gender'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '缺少必要参数'
        ]);
        exit;
    }

    try {
        // 检查手机号是否已注册
        $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->bind_param("s", $postData['phone']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('该手机号已注册');
        }

        // 密码加密
        // $hashedPassword = password_hash($postData['password'], PASSWORD_DEFAULT);
        
        // 插入用户数据
        $stmt = $conn->prepare("INSERT INTO users (nickname, gender, phone, password, avatar_url, register_time) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissss", 
            $postData['nickName'],
            $postData['gender'],
            $postData['phone'],
            $postData['password'],
            $postData['avatarUrl'],
            $postData['registerTime']
        );
//         if (!$stmt->execute()) {
//     throw new Exception("Execute failed: " . $stmt->error);
// }
        if ($stmt->execute()) {
            $userId = $stmt->insert_id;
            
            // 生成token
            $token = bin2hex(random_bytes(32));
            
            // 更新用户token
            $stmt = $conn->prepare("UPDATE users SET token = ? WHERE id = ?");
            $stmt->bind_param("si", $token, $userId);
            $stmt->execute();

            echo json_encode([
                'success' => true,
                'message' => '注册成功',
                'data' => [
                    'userId' => $userId,
                    'token' => $token
                ]
            ]);
        } else {
            throw new Exception('注册失败');
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'userinfo' => $postData
        ]);
    }
    exit;
}

else if (strpos($requestUri, '/mp/user/login') !== false) {
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        // 获取POST数据
        $postData = json_decode(file_get_contents('php://input'), true);
        
        // 验证必填字段
        if (empty($postData['phone']) || empty($postData['password'])) {
            throw new Exception('请输入手机号和密码');
        }

        // 查询用户信息
        $stmt = $conn->prepare("SELECT id, password, avatar_url, nickname, gender, phone FROM users WHERE phone = ?");
        $stmt->bind_param("s", $postData['phone']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // 验证密码
            if ($postData['password'] == $row['password']) {
                // 生成新的token
                $token = bin2hex(random_bytes(32));
                
                // 更新用户token
                $updateStmt = $conn->prepare("UPDATE users SET token = ? WHERE id = ?");
                $updateStmt->bind_param("si", $token, $row['id']);
                $updateStmt->execute();

                echo json_encode([
                    'success' => true,
                    'message' => '登录成功',
                    'data' => [
                        'userId'    => $row['id'],
                        'avatarUrl' => $row['avatar_url'],
                        'nickName'  => $row['nickname'],
                        'gender'    => $row['gender'],
                        'phone'     => $row['phone'],
                        'token'     => $token
                    ]
                ]);
            } else {
                throw new Exception('密码错误');
            }
        } else {
            throw new Exception('用户不存在');
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'userinfo' => $postData,
            'row' => $row
        ]);
    }
    exit;
}
else if (strpos($requestUri, '/mp/user/reset-password') !== false) {
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        // 获取POST数据
        $postData = json_decode(file_get_contents('php://input'), true);
        
        // 验证必填字段
        if (empty($postData['phone']) || empty($postData['password'])) {
            throw new Exception('请填写完整信息');
        }

        // 验证手机号格式
        if (!preg_match("/^1[3-9]\d{9}$/", $postData['phone'])) {
            throw new Exception('手机号格式不正确');
        }

        // 检查用户是否存在
        $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->bind_param("s", $postData['phone']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result->fetch_assoc()) {
            throw new Exception('该手机号未注册');
        }

        // 更新密码
        // $hashedPassword = password_hash($postData['password'], PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE phone = ?");
        $updateStmt->bind_param("ss", $postData['password'], $postData['phone']);

        if ($updateStmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => '密码重置成功'
            ]);
        } else {
            throw new Exception('密码重置失败');
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}
else {
    // 如果路径不匹配，返回 404 错误
    http_response_code(404);
    $error = [
        'message' => '接口不存在'
    ];
    header('Content-Type: application/json');
    echo json_encode($error);
}
?>