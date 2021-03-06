#include "manager.h"

Manager::Manager(int max_process, string process_path, int time_limit):index(1),max_process(max_process),time_limit(time_limit),process_path(process_path){
}

int Manager::StartProcess(){
    while(index<=100||processes.size()!=0){
        while(index<=100&&processes.size()<max_process){
            start_time.push_back(time(NULL));
            processes.push_back(GetNextProcess(index));
            cout<<"push"<<index<<endl;
            index++;
        }
        vector<int> to_delete;
        for(int i=0; i<processes.size();i++){
            auto&& future = processes[i];
            auto status = future.wait_for(std::chrono::milliseconds(0));
            if( status == std::future_status::ready){
//                cout<<"id"<<i<<"ready"<<endl;
                int flag = future.get();
                if(flag){
                    cout<<"run time error";
                    for(int j=i; j<processes.size();j++)processes[j].get();
                    return flag;
                } 
                to_delete.push_back(i);
            }
            else{
                if(index!=100&&time_limit&&start_time[i]+time_limit<time(NULL)){
                    cout<<"time limit exceed";
                    exit(1);
                    return 1;    
                }
//                cout<<"id"<<i<<"running"<<endl;
            }
        }
        for(int i=to_delete.size()-1; i>=0; i--){
            processes.erase(processes.begin()+to_delete[i]);
            start_time.erase(start_time.begin()+to_delete[i]);
        }
//        while(!inp.empty()){
//            printf("%d start\n",inp.front());
//            inp.pop();
//        }
//        while(!out.empty()){
//            printf("%s done\n",out.front().c_str());
//            out.pop();
//        }
        this_thread::sleep_for(std::chrono::milliseconds(50));
    }
    return 0;
}

inline future<int> Manager::GetNextProcess(int _index){
    return  async(std::launch::async,[=]{
        int t = _index%5;
//        inp.push(_index);
        char buff[3];
        sprintf(buff,"%d",_index);
        string cmd = buff;
        while(cmd.size()<4)cmd="0"+cmd;
        cmd = process_path+" img/"+cmd+".bmp";
        
//        std::this_thread::sleep_for(std::chrono::seconds(2));
//        out.push(_index);
        return system(cmd.c_str());;
    });
}
