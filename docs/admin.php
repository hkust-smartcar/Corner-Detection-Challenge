<!--Skygear CDN-->
<script src="https://code.skygear.io/js/polyfill/latest/polyfill.min.js"></script>
<script src="https://code.skygear.io/js/skygear/latest/skygear.min.js"></script>
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://www.gstatic.com/firebasejs/4.8.1/firebase.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.13/vue.js"></script>
<script src="js/moment.min.js"></script>

<script src="grade.js"></script>
<script src="config.js"></script>

<title>Admin Panel - Corner Detection Challenge</title>

<div id="app">
  <h2>Admin Panel</h2>
  <div class="row">
    <a class="waves-effect waves-light btn col s1" onclick="$('#announcements').modal('open')">View All Announcements</a>
    <div class="input-field col s6">
      <input id="announce" type="text" class="validate">
      <label for="announce">announce</label>
    </div>
    <button class="waves-effect waves-light btn col s3" onclick="Announce()">Announce</button>
  </div>
  <div class="row">
    <div class="col s3">
      <h5>Compile Queue</h5>
      <table>
        <thead>
          <tr>
            <th>name</th>
            <th>time</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="submit in queue">
            <td>{{submit.name}}</td>
            <td>{{moment(submit.time).format('HH:mm:ss')}}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="col s4">
      <h5>Compiling</h5>
      <table>
        <thead>
          <tr>
            <th>name</th>
            <th>time</th>
            <th>compile time</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="job in compiling">
            <td>{{job.name}}</td>
            <td>{{moment(job.time).format('HH:mm:ss')}}</td>
            <td>{{moment(job.compile_time).format('HH:mm:ss')}}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="col s5">
      <h5>Compiled</h5>
      <table>
        <thead>
          <tr>
            <th>name</th>
            <th>time</th>
            <th>compile time</th>
            <th>grade time</th>
            <th>Detail</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(submit,k) in compiled">
            <td>{{submit.name}}</td>
            <td>{{moment(submit.time).format('HH:mm:ss')}}</td>
            <td>{{moment(submit.compile_time).format('HH:mm:ss')}}</td>
            <td>{{moment(submit.grade_time).format('HH:mm:ss')}}</td>
            <td>
              <button class="waves-effect waves-light btn" v-on:click="ViewDetail(k)">Details</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div id="detail" class="modal">
    <div class="modal-content">
      <h3>Detail</h3>
      <table v-if="typeof detail_index === 'number'">
        <thead>
          <tr>
            <th>name</th>
            <th>time</th>
            <th>compile time</th>
            <th>grade time</th>
            <th v-if="detail.compile_duration">compile duration</th>
            <th v-if="detail.runtime_duration">run time</th>
            <th v-if="detail.grade">mark</th>
            <th v-if="detail.grade">perfect</th>
            <th v-if="detail.grade">great</th>
            <th v-if="detail.grade">good</th>
            <th v-if="detail.grade">bad</th>
            <th v-if="detail.grade">miss</th>
            <th v-if="detail.grade">extra</th>
            <th v-if="detail.error">error</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{detail.name}}</td>
            <td>{{moment(detail.time).format('HH:mm:ss')}}</td>
            <td>{{moment(detail.compile_time).format('HH:mm:ss')}}</td>
            <td>{{moment(detail.grade_time).format('HH:mm:ss')}}</td>
            <td v-if="detail.compile_duration">{{detail.compile_duration}}</td>
            <td v-if="detail.runtime_duration">{{detail.runtime_duration}}</td>
            <td v-if="detail.grade">{{detail.grade.mark}}</td>
            <td v-if="detail.grade">{{detail.grade.perfect}}</td>
            <td v-if="detail.grade">{{detail.grade.great}}</td>
            <td v-if="detail.grade">{{detail.grade.good}}</td>
            <td v-if="detail.grade">{{detail.grade.bad}}</td>
            <td v-if="detail.grade">{{detail.grade.miss}}</td>
            <td v-if="detail.grade">{{detail.grade.extra}}</td>
            <td v-if="detail.error">{{detail.error}}</td>
          </tr>
        </tbody>
      </table>
      <div v-if="detail.output">
      OUTPUT:<input type="text" :value="detail.output" id="cornerJSON" readonly>
      <button class="waves-effect waves-light btn" onclick="$('input#cornerJSON')[0].select();document.execCommand('Copy')">copy</button></div>
      <div v-else><button class="waves-effect waves-light btn" onclick="GenerateOutput(app.detail)">generate</button></div>
    </div>
    <div class="modal-footer">
      <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">Close</a>
    </div>
  </div>
  <div id="announcements" class="modal">
    <div class="modal-content">
      <table>
        <thead>
          <th>time</th>
          <th>announcement</th>
        </thead>
        <tbody>
          <tr v-for="a in announcements">
            <td>{{a.time}}</td>
            <td>{{a.announce}}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="modal-footer">
      <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">Close</a>
    </div>
  </div>
</div>

<script>

  var users = {}
  var max_parallele = 1

  var app = new Vue({
    el: "#app",
    data: {
      queue: [],
      compiling: [],
      compiled: [],
      detail_index: false,
      announcements: []
    },
    methods: {
      moment: moment,
      ViewDetail(index) {
        this.detail_index = index
        $('#detail').modal('open')
      }
    },
    computed: {
      detail() {
        if (typeof this.detail_index === "number")
          return this.compiled[this.detail_index]
        return {}
      }
    }
  })

  var Announce = () => {
    let announce = $('#announce')[0].value
    app.announcements.push({ time: moment(), announce: announce })
    skygear.pubsub.publish('announce', announce)
    firebase.database().ref('admin/announcements').set(JSON.parse(JSON.stringify(app.announcements)))
  }

  firebase.initializeApp(firebase_config);
  var storage = firebase.storage();
  var storageRef = storage.ref();
  skygear.config(skygear_config).then(() => {

    console.log('skygear container is now ready for making API calls.');
    Login()
    FetchAll()
    skygear.pubsub.on('upload', (obj) => {
      skygear.pubsub.publish(obj.name, "received")
    });
    skygear.pubsub.on('online', (name) => {
      if (name == "?")
        skygear.pubsub.publish('online', 'Y')
    });
    skygear.pubsub.publish('online', 'Y')
  })

  var Login = () => {
    let name = 'Leslie'
    let pw = 'BoyGod'
    console.log('hi')
    skygear.auth.loginWithUsername(name, pw)
      .then((user) => {
        is_login = true
        console.log(user); // user object
      }, (error) => {
        console.error(error);
      })
  }

  var FetchAll = () => {
    ref = firebase.database().ref("users")
    ref.on("value", function (snapshot) {
      console.log(snapshot.val());
      users = snapshot.val()
    }, function (errorObject) {
      console.log("The read failed: " + errorObject.code);
    });
    ref = firebase.database().ref("admin/queue")
    ref.on("value",function (snapshot) {
      console.log(snapshot.val()&&app.compiling.length<max_parallele);
      app.queue = snapshot.val() || []
      if (app.queue.length > 0) {
        CompileSingle()
      }
    }, function (errorObject) {
      console.log("The read failed: " + errorObject.code);
    });
    ref = firebase.database().ref("admin/compiled")
    ref.once("value").then(function (snapshot) {
      console.log(snapshot.val());
      app.compiled = JSON.parse(snapshot.val()) || []
    }, function (errorObject) {
      console.log("The read failed: " + errorObject.code);
    });
    firebase.database().ref("admin/announcements").once("value").then((snapshot) => {
      app.announcements = snapshot.val() || []
    })
  }

  var FetchUser = (name, callback) => {
    ref = firebase.database().ref("users/" + name)
    ref.once("value").then(function (snapshot) {
      console.log(snapshot.val());
      if (callback) callback(snapshot.val())
    }, function (errorObject) {
      console.log("The read failed: " + errorObject.code);
    });
  }

  var SaveUser = (name, obj) => {
    firebase.database().ref('users/' + name).set(obj)
  }

  var PopNext = ()=>{
    let job = null, i=0
    for(i=0; i<app.queue.length; i++){
      job = app.queue[i]
      if(app.compiling.findIndex(o=>o.name==job.name)==-1){
        break
      }
    }
    if(i==app.queue.length)return {name:null,time:null}
    app.queue.splice(i,1)
    firebase.database().ref('admin/queue').set(app.queue)
    return job
  }

  var CompileSingle = () => {
    if (app.queue.length == 0) return
    if(app.compiling.length>=max_parallele) return
    var { name: name, time: time } = PopNext()
    if(name==null)return
    app.compiling.push( { name: name, time: time, compile_time: moment() })
    console.log('compiling', app.compiling)
    console.log(`start compile ${name}`)
    storageRef.child(`files/${name}/${btoa(time)}.cpp`).getDownloadURL().then(function (url) {
      console.log('cpp url', url)
      $.ajax({
        url: `sandbox/compile.php?name=${name}&url=${btoa(url)}`,
        success: (data) => {
          console.log('compile done', data)
          try{
            CompileDoneHandler(data,name,time)
          }
          catch(error){
            ErrorHandler(error, name, time)
          }
        }
      })
    }).catch(function (error) {
      // Handle any errors
      ErrorHandler(error, name, time)
    });
  }

  var ErrorHandler = (error,name,time)=>{
    console.log(error)
      skygear.pubsub.publish(data.name, { type: 'grade', time: time, error: 'fail' })
      let index = app.compiling.findIndex(o=>o.name==name)
      let job = app.compiling.splice(index,1)[0]
      firebase.database().ref(`users/${name}/submits`).once('value').then(snapshot=>{
        submits = snapshot.val().map(s=>{
          if(s.time==time){
            s.status = 'error'
            s.error = 'fail'
          }
          return s
        })
        firebase.database().ref(`users/${name}/submits`).set(submits)
      })
      if (app.queue.length > 0) {
        CompileSingle()
      }
  }

  var CompileDoneHandler = (data,name,time)=>{
    try{
      data = JSON.parse(data)
    }catch(e){
      if(data.indexOf('Maximum execution time')!=-1){
        data = {error:'Maximum execution time exceeded'}
      }
      else{
        data = {error:'unknown error'}
        console.log('unknown error',e)
      }
    }
    let index = app.compiling.findIndex(o=>o.name==name)
    let job = app.compiling.splice(index,1)[0]
    job.grade_time = moment()
    if ('error' in data) {
      console.log('error', data)
      skygear.pubsub.publish(name, { type: 'grade', time: time, error: data.error})
      job.error = data.error
      let temp = {}
      Object.assign(temp, job)
      app.compiled.splice(0, 0, temp)
    }
    else {
      job.compile_duration = data.compile_duration;
      job.runtime_duration = data.runtime_duration;
      ({grade,output} = Grade(data));
      FetchUser(name, (user) => {
        if (mark in user && user.mark >= grade.mark) return
        user.mark = grade.mark
        user.grade = grade
        user.grade_time = time
        SaveUser(name, user)
      })
      skygear.pubsub.publish(name, { type: 'grade', time: time, grade: grade, compile_duration:data.compile_duration,runtime_duration:data.runtime_duration })
      job.grade = grade
      let temp = {}
      //Object.assign(temp,app.compiling)
      for (let prop in job) {
        temp[prop] = job[prop]
      }
      temp.output = output
      app.compiled.splice(0, 0, temp)
    }
    
    console.log(app.queue)
    console.log(app.compiled)
    firebase.database().ref('admin/queue').set(app.queue)
    firebase.database().ref('admin/compiled').set(JSON.stringify(app.compiled))
    firebase.database().ref(`users/${name}/submits`).once('value').then(snapshot=>{
      submits = snapshot.val().map(s=>{
        if(s.time==time){
          if('error' in data){
            s.status = 'error'
            s.error = job.error
          }
          else{
            s.status = 'graded'
            s.grade = job.grade
            s.compile_duration = data.compile_duration
            s.runtime_duration = data.runtime_duration
          }
        }
        return s
      })
      firebase.database().ref(`users/${name}/submits`).set(submits)
    })
    if (app.queue.length > 0) {
      CompileSingle()
    }
  }

  $('.modal').modal()
</script>